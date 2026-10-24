/**
 * Minimal fetch wrapper matching the legacy admin's axios setup (main.js):
 * baseURL /api/, cookies, and — critically — the X-XSRF-TOKEN header on EVERY
 * request (GETs included). The API uses Passport's cookie guard
 * (CreateFreshApiToken / laravel_token cookie), which rejects any request
 * missing a valid CSRF header with a 401. Axios sends this header
 * automatically from the XSRF-TOKEN cookie; fetch does not, so we replicate it.
 *
 * Error policy (mirrors the legacy interceptors):
 *   401 / 419                      hard logout (POST /logout) then /login — the
 *                                  web session may still be alive, and just
 *                                  visiting /login would bounce back to / forever
 *   409 + x-session-revoked: 1     same (Security tab "log out other devices")
 *   403 {status: 'limit_reached'}  SaaS plan limit: open the upgrade modal and
 *                                  stay on the page; the error still rejects
 *   anything else non-2xx          throws {status, data} — the PAGE decides
 *                                  (a 404 on a detail load → not-found page;
 *                                  a failed mutation → inline message)
 */
import { showLimitReached } from './limitReached';

const BASE = '/api/';

function readCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : '';
}

function csrfHeaders() {
    const xsrf = readCookie('XSRF-TOKEN');
    if (xsrf) return { 'X-XSRF-TOKEN': xsrf };
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? { 'X-CSRF-TOKEN': meta.getAttribute('content') } : {};
}

// Tells the backend which locale to render server-side output (Blade PDFs)
// in, so downloaded/printed PDFs match the language selected in the UI. Read
// straight from localStorage ('language', shared with the legacy admin).
function localeHeader() {
    try {
        const l = window.localStorage.getItem('language');
        return l ? { 'X-Pdf-Locale': l } : {};
    } catch (e) {
        return {};
    }
}

function baseHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...csrfHeaders(),
        ...localeHeader(),
    };
}

let redirectingToLogin = false;

/** Clear the web session too, then leave for /login (prevents the loop). */
export async function hardLogoutToLogin() {
    if (redirectingToLogin) return;
    redirectingToLogin = true;
    try { window.localStorage.removeItem('stocky_auth_cache_v1'); } catch (e) { /* ignore */ }
    try {
        await fetch('/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', ...csrfHeaders() },
        });
    } catch (e) { /* still navigate */ }
    window.location.replace('/login');
}

/** Turns a non-2xx response into the thrown error, after the global cases. */
async function failed(res) {
    const err = new Error(`HTTP ${res.status}`);
    err.status = res.status;

    if (res.status === 401 || res.status === 419) {
        hardLogoutToLogin();
        err.unauthenticated = true;
        throw err;
    }
    if (res.status === 409 && res.headers.get('x-session-revoked') === '1') {
        hardLogoutToLogin();
        err.sessionRevoked = true;
        throw err;
    }
    try { err.data = await res.json(); } catch (e) { /* not JSON */ }
    if (res.status === 403 && err.data) {
        if (err.data.status === 'limit_reached') {
            // SaaS plan quota (CheckTenantLimits): upgrade modal, stay on the page.
            err.limitReached = true;
            showLimitReached(err.data.message, 'limit');
        } else if (err.data.status === 'feature_unavailable') {
            // Module not in the plan (CheckTenantFeature): same modal, "Upgrade
            // Required" title — the Blade plan-upgrade-modal partial does this for
            // non-JSON requests; the legacy SPA sent GET loads to its 403 page.
            err.featureUnavailable = true;
            showLimitReached(err.data.message, 'feature');
        } else if (err.data.status === 'subscription_required') {
            // Subscription lapsed mid-session (EnsureActiveSubscription). Every
            // SPA page is behind the same middleware, so the only reachable UI
            // is the Blade billing area — exactly where a full page load would
            // have been redirected.
            err.subscriptionRequired = true;
            redirectToBillingPlans();
        }
    }
    throw err;
}

let redirectingToBilling = false;

function redirectToBillingPlans() {
    if (redirectingToBilling) return;
    redirectingToBilling = true;
    window.location.assign('/billing/plans');
}

async function request(method, url, { params, body, formData, raw } = {}) {
    let full = BASE + url.replace(/^\//, '');
    if (params) {
        const qs = new URLSearchParams();
        Object.entries(params).forEach(([k, v]) => {
            if (v !== undefined && v !== null) qs.append(k, v);
        });
        const s = qs.toString();
        if (s) full += (full.includes('?') ? '&' : '?') + s;
    }

    const headers = baseHeaders();
    let payload;
    if (formData) {
        payload = formData;
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }

    const res = await fetch(full, {
        method,
        credentials: 'same-origin',
        headers,
        body: payload,
    });

    if (!res.ok) await failed(res);
    if (res.status === 204) return raw ? { status: 204, data: null } : null;
    const data = await res.json();
    return raw ? { status: res.status, data } : data;
}

/** GET a binary response (PDF downloads) and save it via a temporary link. */
async function download(url, filename) {
    const res = await fetch(BASE + url.replace(/^\//, ''), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', ...csrfHeaders(), ...localeHeader() },
    });
    if (!res.ok) await failed(res);
    const blob = await res.blob();
    const href = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = href;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(href);
}

/**
 * GET a binary response as a Blob, for previewing (rather than saving) a PDF.
 * The type is coerced so an error page returned as a blob still renders as
 * something rather than silently failing in an iframe.
 */
async function blob(url) {
    const res = await fetch(BASE + url.replace(/^\//, ''), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', ...csrfHeaders(), ...localeHeader() },
    });
    if (!res.ok) await failed(res);
    const raw = await res.blob();
    return raw.type === 'application/pdf' ? raw : new Blob([raw], { type: 'application/pdf' });
}

export default {
    get: (url, params) => request('GET', url, { params }),
    post: (url, body) => request('POST', url, { body }),
    postForm: (url, formData) => request('POST', url, { formData }),
    put: (url, body) => request('PUT', url, { body }),
    patch: (url, body) => request('PATCH', url, { body }),
    delete: (url) => request('DELETE', url),
    download,
    blob,
    /**
     * Same as the verbs above but resolves `{ status, data }` instead of the
     * bare body. The POS axios shim needs the HTTP status (its callers read
     * `response.status`), which the plain verbs discard.
     */
    raw: (method, url, opts = {}) => request(method, url, { ...opts, raw: true }),
};

import MIGRATED_ROUTES from './migratedRoutes.json';

/**
 * Which legacy '/app/…' paths already live in the Vue 3 app, and where.
 *
 * ONE module for both sides of the migration: the new app's sidebar/goTo()
 * (config/menu.js, lib/nav.js) and the LEGACY router guard
 * (resources/src/router.js) import it, so both always agree on who owns a
 * page. Adding a migrated page = a route in router/index.js + one entry in
 * migratedRoutes.json. Keep this file free of Vue 3 imports — the legacy
 * webpack build compiles it too.
 *
 * Keys may carry parameters, e.g.
 *   "/app/reports/detail_customer/:id": "/reports/customers/:id"
 * A ':name' segment matches one path segment and is carried over to the
 * same-named segment of the target. A query string on the input
 * ('/app/service/jobs?status=quoted') is matched without it and appended to
 * the result unchanged, so filtered links keep their filter.
 */
export { MIGRATED_ROUTES };

const PATTERNS = Object.keys(MIGRATED_ROUTES)
    .filter(k => k.includes(':'))
    .map(k => ({ from: k.split('/'), to: MIGRATED_ROUTES[k] }));

/** New-app path for a legacy path, or null when the page is still legacy. */
export function resolveMigratedPath(path) {
    if (!path) return null;
    const str = String(path);
    const qIdx = str.search(/[?#]/);
    const suffix = qIdx >= 0 ? str.slice(qIdx) : '';
    const clean = (qIdx >= 0 ? str.slice(0, qIdx) : str).replace(/\/+$/, '') || '/';

    if (Object.prototype.hasOwnProperty.call(MIGRATED_ROUTES, clean)) return MIGRATED_ROUTES[clean] + suffix;

    const segs = clean.split('/');
    for (const p of PATTERNS) {
        if (p.from.length !== segs.length) continue;
        const params = {};
        let ok = true;
        for (let i = 0; i < segs.length; i++) {
            if (p.from[i].startsWith(':')) params[p.from[i].slice(1)] = segs[i];
            else if (p.from[i] !== segs[i]) { ok = false; break; }
        }
        if (ok) return p.to.replace(/:([A-Za-z_]+)/g, (m, name) => (name in params ? params[name] : m)) + suffix;
    }
    return null;
}

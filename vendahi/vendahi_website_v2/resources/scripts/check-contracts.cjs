#!/usr/bin/env node
/**
 * Contract check for ported pages against THIS backend (read-only):
 *
 *   node resources/scripts/check-contracts.cjs src/pages/reports [more dirs/files]
 *
 * For every http.get/post/put/delete/postForm/download/blob('Ã¢â‚¬Â¦') and
 * useCrudTable('Ã¢â‚¬Â¦', { rowsKey | select }) call in the given files:
 *   1. resolves the endpoint against routes/tenant_api.php (prefix groups and
 *      Route::resource are expanded; `${Ã¢â‚¬Â¦}` segments become wildcards);
 *   2. for GET list endpoints with a rowsKey, finds the controller method and
 *      checks the key appears in its `'key' => Ã¢â‚¬Â¦` response array.
 *
 * Prints one line per problem; exit 1 when anything is unresolved. A page
 * using `select:` is reported as "select (manual)" so it gets a human look.
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..', '..');
const LARAVEL = ROOT;
// tenant_api.php is mounted under /api; routes/portal.php declares its own
// `api/portal` prefix group Ã¢â‚¬â€ the SPAs call both through the /api base, so a
// leading `api/` segment is stripped to compare like with like.
const ROUTE_FILES = ['tenant_api.php', 'portal.php'].map(f => path.join(LARAVEL, 'routes', f));
const CTRL = path.join(LARAVEL, 'app', 'Http', 'Controllers');

// ------------------------------------------------------------ routes
function loadRoutes() {
    const routes = []; // { method, path, action }
    for (const file of ROUTE_FILES) loadRouteFile(file, routes);
    return routes;
}

function loadRouteFile(ROUTES, routes) {
    const src = fs.readFileSync(ROUTES, 'utf8');
    // Track prefix groups by brace depth: Route::Ã¢â‚¬Â¦->prefix('x')->group(function () {
    const prefixStack = [];
    let depth = 0;
    const lines = src.split('\n');
    for (const line of lines) {
        const pm = line.match(/prefix\(\s*['"]([^'"]+)['"]\s*\)[^{]*group\s*\(\s*function[^{]*\{/);
        // count braces before applying
        const opens = (line.match(/\{/g) || []).length;
        const closes = (line.match(/\}/g) || []).length;
        if (pm) prefixStack.push({ prefix: pm[1], depth: depth + 1 });
        depth += opens - closes;
        while (prefixStack.length && prefixStack[prefixStack.length - 1].depth > depth) prefixStack.pop();
        const prefix = prefixStack.map(p => p.prefix).join('/').replace(/^api\/?/, '');
        const full = p => [prefix, p.replace(/^\/+|\/+$/g, '')].filter(Boolean).join('/');
        let m;
        if ((m = line.match(/Route::(get|post|put|patch|delete|any)\(\s*['"]([^'"]*)['"]\s*,\s*(?:'([^']+)'|"([^"]+)"|\[[^\]]*\]|function)/))) {
            routes.push({ method: m[1].toUpperCase(), path: full(m[2]), action: m[3] || m[4] || '(closure)' });
        } else if ((m = line.match(/Route::(resource|apiResource)\(\s*['"]([^'"]+)['"]\s*,\s*(?:['"]([^'"]+)['"]|([\w\\]+)::class)/))) {
            // resource = 7 actions; apiResource = the 5 API ones. The controller
            // may be a quoted string or a ::class reference.
            const api = m[1] === 'apiResource';
            const base = full(m[2]);
            const c = (m[3] || m[4] || '').replace(/^\\?App\\Http\\Controllers\\/, '');
            routes.push({ method: 'GET', path: base, action: c + '@index' });
            if (!api) routes.push({ method: 'GET', path: base + '/create', action: c + '@create' });
            routes.push({ method: 'POST', path: base, action: c + '@store' });
            routes.push({ method: 'GET', path: base + '/{id}', action: c + '@show' });
            if (!api) routes.push({ method: 'GET', path: base + '/{id}/edit', action: c + '@edit' });
            routes.push({ method: 'PUT', path: base + '/{id}', action: c + '@update' });
            routes.push({ method: 'PATCH', path: base + '/{id}', action: c + '@update' });
            routes.push({ method: 'DELETE', path: base + '/{id}', action: c + '@destroy' });
        }
    }
    return routes;
}

function routeMatcher(routePath) {
    const re = '^' + routePath.split('/').map(seg => (seg.startsWith('{') ? '[^/]+' : seg.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))).join('/') + '$';
    return new RegExp(re);
}

// ------------------------------------------------------------ controllers
const ctrlCache = new Map();
function methodKeys(action) {
    if (!action || action === '(closure)') return null;
    const [cls, method] = action.split('@');
    const rel = cls.replace(/\\\\/g, '/').replace(/\\/g, '/') + '.php';
    const file = path.join(CTRL, rel);
    const key = file + '#' + method;
    if (ctrlCache.has(key)) return ctrlCache.get(key);
    if (!fs.existsSync(file)) { ctrlCache.set(key, { missing: file }); return ctrlCache.get(key); }
    const src = fs.readFileSync(file, 'utf8');
    const start = src.search(new RegExp(`function\\s+${method}\\s*\\(`));
    if (start < 0) { ctrlCache.set(key, { missingMethod: method }); return ctrlCache.get(key); }
    // body = until the next "public function" (good enough)
    const rest = src.slice(start);
    const next = rest.slice(10).search(/\n\s*(public|protected|private)\s+function\s/);
    const body = next > 0 ? rest.slice(0, next + 10) : rest;
    const keys = new Set();
    for (const m of body.matchAll(/['"]([A-Za-z_][A-Za-z0-9_]*)['"]\s*=>/g)) keys.add(m[1]);
    const out = { keys, paginator: /paginate\(/.test(body) };
    ctrlCache.set(key, out);
    return out;
}

// ------------------------------------------------------------ pages
function walk(p, out = []) {
    const st = fs.statSync(p);
    if (st.isDirectory()) for (const e of fs.readdirSync(p)) walk(path.join(p, e), out);
    else if (/\.(vue|js)$/.test(p)) out.push(p);
    return out;
}

function normalize(ep) {
    // A literal ending in "/" is followed by a concatenated id ('foo/' + id):
    // treat the missing tail as a wildcard segment.
    return ep.replace(/^\/+/, '').replace(/\$\{[^}]*\}/g, '{x}').replace(/\?.*$/, '').replace(/\/+$/, '/{x}');
}

function calls(src) {
    const out = [];
    for (const m of src.matchAll(/\b(?:http|crud)\.(get|post|put|patch|delete|postForm|download|blob)\(\s*[`'"]([^`'"]+)[`'"]/g)) {
        const method = { get: 'GET', post: 'POST', postForm: 'POST', put: 'PUT', patch: 'PATCH', delete: 'DELETE', download: 'GET', blob: 'GET' }[m[1]];
        out.push({ method, ep: normalize(m[2]) });
    }
    for (const m of src.matchAll(/useCrudTable\(\s*(?:[`'"]([^`'"]+)[`'"]|\(\)\s*=>[^,]+)\s*(?:,\s*\{([\s\S]*?)\}\s*\))?/g)) {
        const opts = m[2] || '';
        const rowsKey = (opts.match(/rowsKey:\s*['"]([^'"]+)['"]/) || [])[1];
        const select = /select\s*:/.test(opts);
        const bulk = (opts.match(/bulkDeleteEndpoint:\s*['"]([^'"]+)['"]/) || [])[1];
        out.push({ method: 'GET', ep: m[1] ? normalize(m[1]) : '(dynamic endpoint)', rowsKey, select, list: true, bulk });
    }
    return out;
}

const routes = loadRoutes();
const matchers = routes.map(r => ({ ...r, re: routeMatcher(r.path) }));
function find(method, ep) {
    return matchers.find(r => (r.method === method || r.method === 'ANY') && r.re.test(ep));
}

const targets = process.argv.length > 2 ? process.argv.slice(2).map(p => path.resolve(p)) : [path.join(ROOT, 'resources', 'src', 'pages')];
let problems = 0;
for (const t of targets) {
    for (const file of walk(t)) {
        const src = fs.readFileSync(file, 'utf8');
        const rel = path.relative(ROOT, file);
        const seen = new Set();
        for (const c of calls(src)) {
            const sig = c.method + ' ' + c.ep + (c.rowsKey || '');
            if (seen.has(sig)) continue;
            seen.add(sig);
            if (c.ep === '(dynamic endpoint)') { console.log(`${rel}: useCrudTable with a dynamic endpoint Ã¢â‚¬â€ check by hand`); continue; }
            if (c.ep.startsWith('{x}')) { console.log(`${rel}: dynamic base ${c.method} ${c.ep} Ã¢â‚¬â€ verify by hand`); continue; }
            let r = find(c.method, c.ep);
            if (!r && c.method === 'POST' && /_method/.test(src)) {
                r = find('PUT', c.ep) || find('PATCH', c.ep);
                if (r) console.log(`${rel}: ${c.ep} POST Ã¢â€ â€™ ${r.method} via _method spoof (ok)`);
            }
            if (!r) { problems++; console.log(`${rel}: NO ROUTE  ${c.method} ${c.ep}`); continue; }
            if (c.list) {
                const mk = methodKeys(r.action);
                if (!mk) continue;
                if (mk.missing) { problems++; console.log(`${rel}: controller file missing for ${r.action}`); continue; }
                if (mk.missingMethod) { problems++; console.log(`${rel}: method missing ${r.action}`); continue; }
                if (c.select) { console.log(`${rel}: ${c.ep} uses select (manual) Ã¢â‚¬â€ controller keys: ${[...mk.keys].slice(0, 12).join(' ')}${mk.paginator ? ' [paginator]' : ''}`); continue; }
                if (c.rowsKey && !mk.keys.has(c.rowsKey)) { problems++; console.log(`${rel}: ROWSKEY '${c.rowsKey}' not in ${r.action} Ã¢â‚¬â€ has: ${[...mk.keys].slice(0, 12).join(' ')}${mk.paginator ? ' [paginator]' : ''}`); }
                if (!c.rowsKey && !c.select) console.log(`${rel}: ${c.ep} auto rows (no rowsKey) Ã¢â‚¬â€ controller keys: ${[...mk.keys].slice(0, 10).join(' ')}`);
                const bulkEp = c.bulk ? normalize(c.bulk) : c.ep + '/delete/by_selection';
                if (/selectable/.test(src) && !find('POST', bulkEp)) console.log(`${rel}: note: no bulk-delete route ${bulkEp} (page is selectable)`);
            }
        }
    }
}
console.log(problems ? `\n${problems} problem(s)` : '\nall contracts resolve');
process.exit(problems ? 1 : 0);

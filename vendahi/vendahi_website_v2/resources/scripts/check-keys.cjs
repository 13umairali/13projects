#!/usr/bin/env node
/**
 * Verifies that every translation key used by the Vue 3 app exists in the
 * English seeder (database/seeders/translations/en.php) Ã¢â‚¬â€ the DB table each
 * tenant loads via GET /api/translations/{locale} is seeded from it Ã¢â‚¬â€ or in
 * one of the bundled front-end bundles (src/i18n/fallback.js, support.js),
 * which are merged under the DB translations at boot.
 *
 *   node resources/scripts/check-keys.cjs            # whole src/
 *   node resources/scripts/check-keys.cjs src/pages/Brands.vue [more files]
 *   node resources/scripts/check-keys.cjs | node resources/scripts/pull-keys.cjs --from-check
 *
 * Scans: $t('key'), t('key'), tf('key'), i18n.global.t('key') and the
 * `label` / `labelKeys` values in config/menu.js (raw: true entries excluded).
 * Calls that pass an inline fallback Ã¢â‚¬â€ t('key', 'Fallback text') Ã¢â‚¬â€ are not
 * flagged: they render readable text even when the key is absent.
 * Keys only used dynamically ($t(variable)) cannot be checked Ã¢â‚¬â€ grep the
 * seeder by hand for those, as the stocky MIGRATION.md notes.
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..', '..');
const SEEDER = path.resolve(ROOT, 'database', 'seeders', 'translations', 'en.php');

const QUOTED = String.raw`(['"])((?:\\.|(?!\1).)*)\1`;

function seederKeys() {
    const php = fs.readFileSync(SEEDER, 'utf8');
    const keys = new Set();
    const re = new RegExp(String.raw`^\s*` + QUOTED + String.raw`\s*=>`, 'gm');
    for (const m of php.matchAll(re)) keys.add(m[2].replace(/\\(['"])/g, '$1'));
    return keys;
}

function bundledKeys(keys) {
    for (const bundle of ['fallback.js', 'support.js']) {
        const p = path.join(ROOT, 'resources', 'src', 'i18n', bundle);
        if (!fs.existsSync(p)) continue;
        const src = fs.readFileSync(p, 'utf8');
        const en = src.match(/const en = \{([\s\S]*?)\n\};/);
        if (!en) continue;
        for (const m of en[1].matchAll(/^\s*([A-Za-z_]\w*)\s*:/gm)) keys.add(m[1]);
        for (const m of en[1].matchAll(/^\s*"((?:\\.|[^"\\])*)"\s*:/gm)) keys.add(m[1].replace(/\\(["\\])/g, '$1'));
    }
}

function walk(dir, out = []) {
    for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
        const p = path.join(dir, e.name);
        if (e.isDirectory()) walk(p, out);
        else if (/\.(vue|js)$/.test(e.name)) out.push(p);
    }
    return out;
}

function usedKeys(file) {
    const src = fs.readFileSync(file, 'utf8');
    const found = new Map(); // key -> first line
    const add = (key, idx) => {
        if (!found.has(key)) found.set(key, src.slice(0, idx).split('\n').length);
    };
    // group 3 = an inline fallback argument follows Ã¢â€ â€™ readable without the key
    const callRe = new RegExp(String.raw`(?:\$t|\btf?|i18n\.global\.t)\(\s*` + QUOTED + String.raw`(\s*,\s*['"\x60])?`, 'g');
    for (const m of src.matchAll(callRe)) {
        if (m[3]) continue;
        // $t('cat_' + record.category) Ã¢â‚¬â€ a dynamic key; only its prefix is literal.
        if (/^\s*\+/.test(src.slice(m.index + m[0].length))) continue;
        add(m[2], m.index);
    }
    if (file.endsWith(path.join('config', 'menu.js'))) {
        for (const m of src.matchAll(/\{[^{}]*label:\s*'([^']+)'[^{}]*\}/g)) {
            if (/raw:\s*true/.test(m[0])) continue;
            const lk = m[0].match(/labelKeys:\s*\[([^\]]+)\]/);
            if (lk) for (const k of lk[1].matchAll(/'([^']+)'/g)) add(k[1], m.index);
            else add(m[1], m.index);
        }
    }
    return found;
}

const keys = seederKeys();
bundledKeys(keys);
// The client portal (src/portal) has its own bundled messages
// (src/portal/messages.js, generated from stocky's lang/{locale}/portal.php)
// Ã¢â‚¬â€ its $t() keys are checked against that file instead of the seeder.
const portalKeys = new Set();
{
    const pm = path.join(ROOT, 'resources', 'src', 'portal', 'messages.js');
    if (fs.existsSync(pm)) {
        const src = fs.readFileSync(pm, 'utf8');
        const json = src.slice(src.indexOf('export default ') + 15).replace(/;\s*$/, '');
        try { for (const k of Object.keys(JSON.parse(json).en || {})) portalKeys.add(k); } catch (e) { /* regenerate */ }
    }
}
const isPortal = f => path.resolve(f).startsWith(path.join(ROOT, 'resources', 'src', 'portal') + path.sep);
const files = process.argv.length > 2
    ? process.argv.slice(2).map(f => path.resolve(f))
    : walk(path.join(ROOT, 'resources', 'src'));

let missing = 0;
for (const f of files) {
    for (const [key, line] of usedKeys(f)) {
        if (!(isPortal(f) ? portalKeys : keys).has(key)) {
            missing++;
            console.log(`${path.relative(ROOT, f)}:${line}  missing key: ${key}`);
        }
    }
}
console.log(missing ? `\n${missing} missing key(s)` : 'all keys present');
process.exit(missing ? 1 : 0);

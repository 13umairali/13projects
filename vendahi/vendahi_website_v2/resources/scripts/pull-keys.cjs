#!/usr/bin/env node
/**
 * Copy translation keys from the stocky reference seeders (en/fr/es/ar) into
 * src/i18n/fallback.js Ã¢â‚¬â€ for keys the SaaS seeder lacks (no backend edits).
 *
 *   node resources/scripts/pull-keys.cjs KEY [KEYÃ¢â‚¬Â¦]
 *   node resources/scripts/pull-keys.cjs --file missing.txt      # one key per line (keys may contain spaces)
 *   node resources/scripts/check-keys.cjs | node resources/scripts/pull-keys.cjs --from-check   # pipe the checker output
 *
 * Keys absent from stocky's English seeder are listed at the end and must be
 * added to fallback.js by hand (or the page given a tf(key, fallback)).
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..', '..');
const FALLBACK = path.join(ROOT, 'resources', 'src', 'i18n', 'fallback.js');
const STOCKY = 'D:/laragon/www/stocky/database/seeders/translations';
const LOCALES = ['en', 'fr', 'es', 'ar'];

function readKeys() {
    const args = process.argv.slice(2);
    if (args[0] === '--file') return fs.readFileSync(args[1], 'utf8').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
    if (args[0] === '--from-check') {
        const input = fs.readFileSync(0, 'utf8');
        return [...input.matchAll(/missing key: (.+)$/gm)].map(m => m[1].trim());
    }
    return args;
}
const keys = [...new Set(readKeys())];
if (!keys.length) { console.error('no keys given'); process.exit(1); }

const seeders = Object.fromEntries(LOCALES.map(l => [l, fs.readFileSync(path.join(STOCKY, l + '.php'), 'utf8')]));
const escapeRe = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

function lookup(loc, key) {
    const re = new RegExp(String.raw`^\s*(['"])` + escapeRe(key) + String.raw`\1\s*=>\s*(['"])((?:\\.|(?!\2).)*)\2`, 'm');
    const m = seeders[loc].match(re);
    return m ? m[3].replace(/\\(['"\\])/g, '$1') : undefined;
}

const values = {};
const notFound = [];
for (const k of keys) {
    const en = lookup('en', k);
    if (en === undefined) { notFound.push(k); continue; }
    values[k] = { en };
    for (const loc of LOCALES.slice(1)) values[k][loc] = lookup(loc, k) ?? en;
}

let src = fs.readFileSync(FALLBACK, 'utf8');
const found = Object.keys(values);
let added = 0;
for (const loc of LOCALES) {
    const start = src.indexOf(`const ${loc} = {`);
    const end = src.indexOf('\n};', start);
    const block = src.slice(start, end);
    const lines = found
        .filter(k => !block.includes(`${JSON.stringify(k)}:`) && !new RegExp(`^\\s*${escapeRe(k)}\\s*:`, 'm').test(block))
        .map(k => `    ${/^[A-Za-z_]\w*$/.test(k) ? k : JSON.stringify(k)}: ${JSON.stringify(values[k][loc])},`);
    if (loc === 'en') added = lines.length;
    src = src.slice(0, end) + (lines.length ? '\n' + lines.join('\n') : '') + src.slice(end);
}
fs.writeFileSync(FALLBACK, src);
console.log(`fallback.js: +${added} keys (${found.length} found in stocky seeders)`);
if (notFound.length) {
    console.log(`NOT IN STOCKY EN SEEDER (${notFound.length}) Ã¢â‚¬â€ add by hand:`);
    for (const k of notFound) console.log('  ' + k);
}

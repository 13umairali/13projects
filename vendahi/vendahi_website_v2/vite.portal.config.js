import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = __dirname;

/**
 * Client Portal (Phase 10) — its own tiny SPA, built separately from the admin.
 *
 * Fixed output names (app.js / app.css, no manifest): resources/views/portal.blade.php
 * loads them with a filemtime() query. Unlike the admin entry that is safe here
 * because the bundle has NO lazy chunks (inlineDynamicImports) — nothing else
 * imports the entry by URL, so a `?v=` query cannot fork the module graph.
 *
 * Output: public/js/portal/ (`npm run build` cleans public/js once, then
 * builds all four bundles into it).
 */
export default defineConfig({
    plugins: [vue()],
    publicDir: false,
    build: {
        outDir: resolve(ROOT, 'public/js/portal'),
        emptyOutDir: true,
        cssCodeSplit: false,
        manifest: false,
        rollupOptions: {
            input: resolve(__dirname, 'resources/src/portal/main.js'),
            output: {
                inlineDynamicImports: true,
                entryFileNames: 'app.js',
                assetFileNames: 'app.[ext]',
            },
        },
    },
});

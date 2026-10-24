import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = __dirname;

/**
 * Builds the Vue 3 + Ant Design admin into public/js/.
 *
 * Filenames are content-hashed and layouts/master.blade.php reads
 * .vite/manifest.json to find them. That is not cosmetic: the entry must be
 * loaded WITHOUT a `?v=` cache-busting query. Lazy chunks import the entry as
 * "../app.js" (no query), and browsers key ES modules by full URL — so a query
 * on the entry loads a SECOND copy of the app (and of Vue) for chunk code,
 * whose currentRenderingInstance is always null ("Cannot read properties of
 * null (reading 'ce')" in renderSlot). Hashed names give cache-busting without
 * a query.
 */
export default defineConfig({
    plugins: [vue()],
    base: '/js/',
    // Never copy a publicDir into the output — we build INTO Laravel's public/.
    publicDir: false,
    css: {
        preprocessorOptions: {
            scss: {
                // The POS (Phase 9) compiles legacy Bootstrap 4 + the legacy app
                // skin verbatim; those sources use @import/darken()/map-get().
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'slash-div'],
                quietDeps: true,
            },
        },
    },
    build: {
        // The shared core (Vue + Ant Design Vue + i18n) legitimately exceeds
        // Vite's default 500 kB notice; manualChunks is OFF LIMITS (see below).
        chunkSizeWarningLimit: 2500,
        outDir: resolve(ROOT, 'public/js'),
        // All four builds share public/js; `npm run build` cleans it once first
        // (clean:js), so each config leaves the others' output in place.
        emptyOutDir: false,
        cssCodeSplit: false,
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/src/main.js'),
            output: {
                // NO manualChunks. Hand-slicing node_modules into vendor buckets
                // split a circular import across chunks and broke module init
                // order ("Cannot access 'Jt' before initialization"). Rollup's
                // automatic splitting already shares code between lazy pages.
                entryFileNames: 'app.[hash].js',
                chunkFileNames: 'chunks/[name].[hash].js',
                assetFileNames: 'assets/[name].[hash][extname]',
            },
        },
    },
});

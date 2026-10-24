import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = __dirname;

/**
 * POS Customer Display (Phase 10) — standalone second-screen app, no router,
 * no Ant Design. Same fixed-name / no-manifest / no-lazy-chunk layout as the
 * portal (see vite.portal.config.js); loaded by resources/views/customer_display.blade.php.
 * Output: public/js/customer-display/.
 */
export default defineConfig({
    plugins: [vue()],
    publicDir: false,
    build: {
        outDir: resolve(ROOT, 'public/js/customer-display'),
        emptyOutDir: true,
        cssCodeSplit: false,
        manifest: false,
        rollupOptions: {
            input: resolve(__dirname, 'resources/src/customer-display/main.js'),
            output: {
                inlineDynamicImports: true,
                entryFileNames: 'app.js',
                assetFileNames: 'app.[ext]',
            },
        },
    },
});

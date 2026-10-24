import { defineConfig } from 'vite';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = __dirname;

/**
 * Storefront (Phase 10) — Alpine.js + Tailwind, NOT a Vue app. The source is
 * untouched (resources/src/storefront.js + resources/css/storefront.css); only
 * the bundler moved from Laravel Mix to Vite, exactly like the stocky reference.
 *
 * The Tailwind config is the project-root tailwind.config.js (its `content`
 * globs resolve from the project root, where the build runs). Outputs, loaded
 * by resources/views/layouts/store.blade.php:
 *   public/js/storefront.min.js   (IIFE, single file)
 *   public/css/storefront.css
 */
export default defineConfig({
    root: ROOT,
    publicDir: false,
    css: {
        postcss: {
            plugins: [
                tailwindcss(resolve(ROOT, 'tailwind.config.js')),
                autoprefixer(),
            ],
        },
    },
    build: {
        outDir: resolve(ROOT, 'public'),
        emptyOutDir: false,
        cssCodeSplit: false,
        manifest: false,
        rollupOptions: {
            input: resolve(ROOT, 'resources/src/storefront.js'),
            output: {
                format: 'iife',
                inlineDynamicImports: true,
                entryFileNames: 'js/storefront.min.js',
                assetFileNames: (info) => {
                    const name = (info.names && info.names[0]) || info.name || '';
                    return name.endsWith('.css') ? 'css/storefront.css' : 'js/assets/[name][extname]';
                },
            },
        },
    },
});

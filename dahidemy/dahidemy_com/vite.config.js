import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                local('Instrument Sans', {
                    variants: [
                        {
                            weight: 400,
                            style: 'normal',
                            src: [
                                'resources/fonts/instrument-sans/instrument-sans-400-normal-DRC__1Mx.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-400-normal-Q_nF8v4l.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-400-normal-D1W7dsQl.woff',
                                'resources/fonts/instrument-sans/instrument-sans-400-normal-r32jotim.woff',
                            ],
                        },
                        {
                            weight: 500,
                            style: 'normal',
                            src: [
                                'resources/fonts/instrument-sans/instrument-sans-500-normal-Dk9ku72i.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-500-normal-CTEe1bJa.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-500-normal-Z6ESRlEs.woff',
                                'resources/fonts/instrument-sans/instrument-sans-500-normal-CAxz3nsc.woff',
                            ],
                        },
                        {
                            weight: 600,
                            style: 'normal',
                            src: [
                                'resources/fonts/instrument-sans/instrument-sans-600-normal-B7fBEWYG.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-600-normal-BsaQcF38.woff2',
                                'resources/fonts/instrument-sans/instrument-sans-600-normal-B9e8oLYv.woff',
                                'resources/fonts/instrument-sans/instrument-sans-600-normal-DMks36a2.woff',
                            ],
                        },
                    ],
                }),
            ],
        }),
        tailwindcss(),
    ],
    // Pin an inline (empty) PostCSS config so Vite does not walk up the
    // directory tree and load the parent backend project's postcss.config.js.
    css: {
        postcss: {},
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

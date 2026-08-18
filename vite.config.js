import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // partnership.css is a dedicated entry so form UI cannot be dropped
            // by PostCSS @import ordering rules (imports must be first).
            input: [
                'resources/css/app.css',
                'resources/css/partnership.css',
                'resources/js/app.js',
                'resources/js/charts.js',
            ],
            refresh: true,
        }),
    ],
});

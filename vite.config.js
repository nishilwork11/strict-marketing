import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                // Downloaded at build time and served from this site, with a size-matched fallback
                google('Hanken Grotesk', {
                    variable: '--font-hanken',
                    weights: ['100..900'],
                }),
                // Display serif for headings, paired with Hanken Grotesk for body
                google('Fraunces', {
                    variable: '--font-fraunces',
                    weights: ['300..600'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

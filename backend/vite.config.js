import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Poppins', {
                    weights: [400, 500, 600, 700],
                    preload: [{ weight: 500 }],
                }),
                bunny('Raleway', {
                    weights: [300, 400, 500, 600, 700, 900],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 700, style: 'normal' }],
                }),
                bunny('Open Sans', {
                    weights: [400, 600, 700],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 400, style: 'normal' }],
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

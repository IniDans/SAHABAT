import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Hasil build dan file `hot` ditulis ke backend/public, karena folder itu
// yang dilayani web server Laravel.
export default defineConfig({
    plugins: [
        laravel({
            input: ['css/app.css', 'js/app.js', 'js/editor.js'],
            publicDirectory: '../backend/public',
            refresh: ['views/**', '../backend/routes/**'],
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
    build: {
        // Folder build ada di luar frontend, jadi Vite tidak mengosongkannya sendiri.
        emptyOutDir: true,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

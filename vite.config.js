import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/landlord/bulk-edit-units.css',
                'resources/js/landlord/bulk-edit-units.js',
                'resources/js/unit-type-bedroom-autofill.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

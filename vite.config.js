import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    
    // Konfigurasi untuk GitHub Actions
    build: {
        // Menonaktifkan sourcemap untuk produksi
        sourcemap: false,
        // Mengoptimasi build
        minify: 'terser',
        // Konfigurasi rollup yang lebih aman untuk lingkungan CI
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: [
                        'bootstrap',
                        'admin-lte'
                    ]
                }
            }
        }
    }
});

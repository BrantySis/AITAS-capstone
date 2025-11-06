import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true, // enables automatic refresh
        }),
    ],
    server: {
        host: '0.0.0.0', // allow access from network
        port: 5173,
        https: {
            key: fs.readFileSync(path.resolve(__dirname, 'facerec-service/certs/aitas-capstone.test.key')),
            cert: fs.readFileSync(path.resolve(__dirname, 'facerec-service/certs/aitas-capstone.test.crt')),
        },
        hmr: {
            protocol: 'wss',                   // secure WebSocket for HMR
            host: 'aitas-capstone.test',       // must match browser hostname
            port: 5173,                        // same as Vite server port
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: undefined,       // optional: avoids code splitting
            },
        },
    },
});

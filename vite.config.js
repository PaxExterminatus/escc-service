import laravel from 'laravel-vite-plugin'
import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
const config = require('./webpack.config');

export default defineConfig({
    plugins: [
        vue(),
        laravel([
            'resources/sass/app.sass',
            'resources/js/app.js',
        ]),
    ],
    refresh: true,
    resolve: {
        extensions: [
            ".js",
            ".ts",
            ".json",
            ".vue",
            ".sass",
        ],
        alias: config.resolve.alias,
    },
});

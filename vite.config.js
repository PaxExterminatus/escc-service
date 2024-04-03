import laravel from 'laravel-vite-plugin'
import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'

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
        alias: {
            'cmp': __dirname + '/resources/js/components',
        },
    },
});

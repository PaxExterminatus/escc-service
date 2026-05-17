import laravel from 'laravel-vite-plugin'
import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    server: {
        host: '127.0.0.1',
    },
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
            'app': __dirname + '/resources/js/app',
            'menu': __dirname + '/resources/js/app/menu',
            'cmp': __dirname + '/resources/js/components',
            'page': __dirname + '/resources/js/components/page',
            'element': __dirname + '/resources/js/components/element',
        },
    },
});

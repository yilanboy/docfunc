import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // typescript
                'resources/ts/ckeditor/ckeditor.ts',
                'resources/ts/sharer.ts',
                'resources/ts/highlight.ts',
                'resources/ts/tagify.ts',
                'resources/ts/scroll-to-top-btn.ts',
                'resources/ts/reader-helpers/code-block-helper.ts',
                'resources/ts/reader-helpers/image-block-helper.ts',
                'resources/ts/oembed/embed-youtube-oembed.ts',
                'resources/ts/oembed/embed-twitter-oembed.ts',
                'resources/ts/progress-bar.ts',
                'resources/ts/scroll-to-anchor.ts',
                'resources/ts/post-outline.ts',
                // css
                'resources/css/app.css',
                'node_modules/@yaireo/tagify/dist/tagify.css',
                'node_modules/highlight.js/styles/atom-one-dark.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

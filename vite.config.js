import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig, lazyPlugins } from 'vite-plus';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: [
                // typescript
                'resources/ts/app.ts',
                'resources/ts/ckeditor/ckeditor.ts',
                'resources/ts/sharer.ts',
                'resources/ts/shiki.ts',
                'resources/ts/tagify.ts',
                'resources/ts/scroll-to-top-btn.ts',
                'resources/ts/reader-helpers/code-block-helper.ts',
                'resources/ts/reader-helpers/image-block-helper.ts',
                'resources/ts/oembed/embed-youtube-oembed.ts',
                'resources/ts/oembed/embed-twitter-oembed.ts',
                'resources/ts/progress-bar.ts',
                'resources/ts/scroll-to-anchor.ts',
                'resources/ts/post-outline.ts',
                'resources/ts/webauthn.ts',
                'resources/ts/markdown-helper.ts',
                'resources/ts/mermaid.ts',
                // css
                'resources/css/app.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ]),
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});

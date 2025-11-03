<?php

use App\Models\Post;
use App\Services\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->contentService = $this->app->make(ContentService::class);
});

it('can filter the dangerous HTML element', function () {
    $body = <<<'HTML'
        <body onload="alert('this a xss attack')">
            <script>alert('this a xss attack');</script>
            <button type="button" onclick="alert('this an another xss attack')"></button>
            <!-- a=&\#X41 (UTF-8) -->
            <IMG SRC=j&#X41vascript:alert('test2')>
            <span>This normal tag</span>
        </body>
    HTML;

    expect($this->contentService->getPurifiedBody($body))
        ->not->toContain('<body onload="alert(\'this a xss attack\')">')
        ->not->toContain('<script>alert(\'this a xss attack\');</script>')
        ->not->toContain('<IMG SRC=j&#X41vascript:alert(\'test2\')>')
        ->toContain('<span>This normal tag</span>');
});

it('can keep the custom HTML elements we want', function () {
    $body = <<<'HTML'
        <body>
            <p style="text-align:right;">by my friend</p>

            <span class="text-tiny">Could you keep it down, please?</span>

            <pre data-language="Bash" spellcheck="false">
                <code class="language-bash">mkdir highlight-blade</code>
            </pre>

            <a href="https://google.com">Google</a>

            <figure class="media image-block-helper-added group relative" style="width:75%;">
                <img src="image.jpg" alt="share">
                <figcaption>Share Image</figcaption>
            </figure>

            <oembed url="https://www.youtube.com/watch?v=rvln9U9w8ZI" class="oembed-processed"></oembed>
        </body>
    HTML;

    expect($this->contentService->getPurifiedBody($body))
        ->toContain('<p style="text-align:right;">by my friend</p>')
        ->toContain('<span class="text-tiny">Could you keep it down, please?</span>')
        ->toContain('<pre data-language="Bash" spellcheck="false">')
        ->toContain('<code class="language-bash">mkdir highlight-blade</code>')
        ->toContain('<a href="https://google.com" rel="noopener noreferrer" target="_blank">Google</a>')
        ->toContain('<figure class="media image-block-helper-added group relative" style="width:75%;">')
        ->toContain('<figcaption>Share Image</figcaption>')
        ->toContain('<oembed url="https://www.youtube.com/watch?v&#61;rvln9U9w8ZI" class="oembed-processed"></oembed>');
});

it('can find all images in the post body', function () {
    $fakeImageNames = [
        '2023_01_01_10_18_21_63b0ed6d06d52.jpg',
        '2022_12_30_22_39_21_63aef81999216.jpg',
        '2022_12_31_10_28_00_63af9e3067169.jpg',
    ];

    $body = <<<HTML
        <div id="fake-post-body">
            <img src="https://fake-url.com/images/{$fakeImageNames[0]}" alt="{$fakeImageNames[0]}" title="" style="">
            <img src="https://fake-url.com/images/{$fakeImageNames[1]}" alt="{$fakeImageNames[1]}" title="" style="">
            <img src="https://fake-url.com/images/{$fakeImageNames[2]}" alt="{$fakeImageNames[2]}" title="" style="">
        </div>
    HTML;

    $post = Post::factory()->create(['body' => $body]);

    expect($this->contentService->getImagesInContent($post->body))
        ->toBeArray()
        ->not->toBeEmpty()
        ->toBe($fakeImageNames);
});

it('will return empty array if no images in the post body', function () {
    $body = <<<'HTML'
        <div id="fake-post-body">
            <p>There is no image in this body</p>
        </div>
    HTML;

    $post = Post::factory()->create(['body' => $body]);

    expect($this->contentService->getImagesInContent($post->body))
        ->toBeArray()
        ->toBeEmpty();
});

it('returns at least 1 minute for empty content', function () {
    expect(ContentService::getReadTime(''))->toBe(1)
        ->and(ContentService::getReadTime('   '))->toBe(1)
        ->and(ContentService::getReadTime('<p></p>'))->toBe(1);
});

it('calculates read time for English only content', function () {
    $englishContent = str_repeat('word ', 200); // 200 words

    expect(ContentService::getReadTime($englishContent))->toBe(1); // 200 words / 200 WPM = 1 minute
});

it('calculates read time for Chinese only content', function () {
    $chineseContent = str_repeat('中', 300); // 300 Chinese characters

    expect(ContentService::getReadTime($chineseContent))->toBe(1); // 300 chars / 300 CPM = 1 minute
});

it('calculates read time for mixed English and Chinese content', function () {
    $mixedContent = str_repeat('word ', 100).str_repeat('中', 150); // 100 English words + 150 Chinese chars

    // 100/200 + 150/300 = 0.5 + 0.5 = 1 minute
    expect(ContentService::getReadTime($mixedContent))->toBe(1);
});

it('calculates read time for programming content with technical terms', function () {
    $programmingContent = <<<'CONTENT'
        function getUserData() {
            const user_name = "john_doe";
            const userId = get-user-id();
            return fetch('/api/users/' + userId);
        }

        CSS classes like .btn-primary and #nav-bar are common.
        Variables like $variable_name and function_name() should be counted.
    CONTENT;

    // Should count programming terms correctly
    expect(ContentService::getReadTime($programmingContent))->toBeGreaterThan(0);
});

it('handles HTML content by stripping tags', function () {
    $htmlContent = <<<'HTML'
        <div>
            <h1>Title with HTML</h1>
            <p>This is a <strong>paragraph</strong> with <em>formatting</em>.</p>
            <code>const variable = "value";</code>
        </div>
    HTML;

    // Should strip HTML and count only the text content
    expect(ContentService::getReadTime($htmlContent))->toBeGreaterThan(0);
});

it('handles HTML entities correctly', function () {
    $contentWithEntities = 'This &amp; that &lt;tag&gt; &quot;quotes&quot; &#39;apostrophe&#39;';

    // Should decode entities before counting
    expect(ContentService::getReadTime($contentWithEntities))->toBeGreaterThan(0);
});

it('rounds up partial minutes', function () {
    // 50 words should give 50/200 = 0.25 minutes, rounded up to 1
    $shortContent = str_repeat('word ', 50);

    expect(ContentService::getReadTime($shortContent))->toBe(1);
});

it('calculates longer read times correctly', function () {
    // 800 English words should give 800/200 = 4 minutes
    $longContent = str_repeat('word ', 800);

    expect(ContentService::getReadTime($longContent))->toBe(4);
});

it('handles real blog content with code examples', function () {
    $blogContent = <<<'CONTENT'
        # Laravel Blade Components

        Laravel Blade components provide a convenient way to create reusable UI elements.

        ```php
        class AlertComponent extends Component
        {
            public function __construct(
                public string $type = 'info',
                public string $message = ''
            ) {}

            public function render()
            {
                return view('components.alert');
            }
        }
        ```

        To use this component in your blade templates:

        ```blade
        <x-alert type="success" message="Operation completed successfully!" />
        ```

        這是一個中文段落，用來測試混合語言的閱讀時間計算。程式碼範例包含了 PHP 和 Blade 的語法。
    CONTENT;

    $readTime = ContentService::getReadTime($blogContent);

    expect($readTime)->toBeGreaterThan(0)
        ->and($readTime)->toBeLessThan(10); // Should be reasonable for this content
});

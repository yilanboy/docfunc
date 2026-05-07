<?php

use App\Models\Post;

test('user can see post outline', function () {
    $body = <<<'HTML'
    <h2>This is post-title 1</h2>
    <p>This is post-body 1</p>
    <h2>This is post-title 2</h2>
    <p>This is post-body 2</p>
    HTML;


    $post = Post::factory()->create([
        'body' => $body,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->assertSee('目錄')
        ->assertSeeLink('This is post-title 1')
        ->assertSeeLink('This is post-title 2');
});

test('user cannot see post outline, if there is no heading', function () {
    $body = <<<'HTML'
    <p>This is a post-body</p>
    HTML;

    $post = Post::factory()->create([
        'body' => $body,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->assertDontSee('目錄');
});

test('right scroll indicator appears when a code block overflows horizontally', function () {
    // a monospace line >150 chars is wider than the 768px center column
    $longLine = str_repeat('echo("aaaaaaaaaaaaaaaa"); ', 12);

    $body = <<<HTML
    <pre><code class="language-php">{$longLine}</code></pre>
    HTML;

    $post = Post::factory()->create(['body' => $body]);

    $page = $this->visit($post->link_with_slug);
    $page->assertSee($post->title);

    // Shiki highlight + fonts.ready + ResizeObserver all need to settle
    // before the indicator opacity flips, so poll instead of guessing a wait().
    $opacity = $page->script(<<<'JS'
        new Promise((resolve) => {
            const deadline = Date.now() + 5000;
            const check = () => {
                const el = document.querySelector('.scroll-indicator-right');
                if (el && el.style.opacity === '1') {
                    return resolve('1');
                }
                if (Date.now() > deadline) {
                    return resolve(el ? el.style.opacity : 'missing');
                }
                requestAnimationFrame(check);
            };
            check();
        })
    JS
    );

    expect($opacity)->toBe('1');
});

test('scroll indicators flip as the user scrolls a code block', function () {
    $longLine = str_repeat('echo("aaaaaaaaaaaaaaaa"); ', 12);

    $body = <<<HTML
    <pre><code class="language-php">{$longLine}</code></pre>
    HTML;

    $post = Post::factory()->create(['body' => $body]);

    $page = $this->visit($post->link_with_slug);
    $page->assertSee($post->title);

    // wait for fonts AND for the right indicator to flip to '1'.
    // Returns the indicator opacity so we can fail fast if it never settled.
    $ready = $page->script(<<<'JS'
        new Promise(async (resolve) => {
            await document.fonts.ready;
            const deadline = Date.now() + 5000;
            const check = () => {
                const right = document.querySelector('.scroll-indicator-right');
                if (right && right.style.opacity === '1') return resolve('1');
                if (Date.now() > deadline) return resolve(right ? right.style.opacity : 'missing');
                requestAnimationFrame(check);
            };
            check();
        })
    JS
    );
    expect($ready)->toBe('1');

    // scroll to the far right (999999 → browser clamps to true max scroll)
    $state = $page->script(<<<'JS'
        new Promise((resolve) => {
            const pre = document.querySelector('pre.shiki');
            pre.addEventListener('scroll', () => {
                requestAnimationFrame(() => {
                    resolve({
                        left: document.querySelector('.scroll-indicator-left').style.opacity,
                        right: document.querySelector('.scroll-indicator-right').style.opacity,
                    });
                });
            }, { once: true });
            pre.scrollLeft = 999999;
        })
    JS
    );

    // at the far right: left indicator visible, right indicator hidden
    expect($state)->toMatchArray(['left' => '1', 'right' => '0']);
});

test('reading progress bar starts at 0 and advances as user scrolls', function () {
    // long body so the post is taller than the viewport
    $body = str_repeat('<p>'.fake()->paragraph(20).'</p>', 30);

    $post = Post::factory()->create(['body' => $body]);

    $page = $this->visit($post->link_with_slug);

    // assertSee on the title naturally waits for the isReady gate to flip
    // (title sits inside x-show="isReady"), so by the time this passes,
    // setupProgressBar has run on a laid-out section.
    $page->assertSee($post->title)
        ->assertAttribute('[role=progressbar]', 'aria-valuenow', '0');

    // scroll halfway down and let the scroll handler fire
    $page->script('window.scrollTo(0, document.documentElement.scrollHeight / 2)');
    $page->wait(1);

    $progress = (int) $page->script(
        'document.querySelector("[role=progressbar]").getAttribute("aria-valuenow")'
    );

    expect($progress)->toBeGreaterThan(0);
});

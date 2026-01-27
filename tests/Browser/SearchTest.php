<?php


use App\Models\Post;

test('user can search posts by title', function () {
    Post::factory()->create([
        'title' => 'this is a news',
    ]);

    $page = $this->visit(route('posts.index'));

    $page->click('#search-button')
        ->type('#search-box', 'news')
        ->assertSeeIn('#search-result', 'this is a news');
});

test('user can search posts by body', function () {
    Post::factory()->create([
        'title' => 'post title',
        'body'  => 'this is a post body containing keyword',
    ]);

    $page = $this->visit(route('posts.index'));

    $page->click('#search-button')
        ->type('#search-box', 'keyword')
        ->assertSeeIn('#search-result', 'post title');
});

test('user can see no result message if there are no results for the search query', function () {
    $page = $this->visit(route('posts.index'));

    $page->click('#search-button')
        ->type('#search-box', 'nonexistentkeyword')
        ->assertSeeIn('#search-result', '抱歉... 找不到 "')
        ->assertSeeIn('#search-result', 'nonexistentkeyword')
        ->assertSeeIn('#search-result', '" 的相關文章');
});

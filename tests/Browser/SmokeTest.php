<?php

it('may show the app name', function () {
    $page = visit('/');

    $page->assertSee(config('app.name'));
});

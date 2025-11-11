<?php

it('displays the app name', function () {
    $page = visit('/');

    $page->assertSee(config('app.name'));
});

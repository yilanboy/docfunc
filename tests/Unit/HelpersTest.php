<?php

it('can generate gravatar url', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = get_gravatar($email);

    expect($url)->toBe("https://www.gravatar.com/avatar/{$hash}?s=64&d=mp&r=g");
});

it('can generate gravatar url with custom size', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = get_gravatar($email, 100);

    expect($url)->toBe("https://www.gravatar.com/avatar/{$hash}?s=100&d=mp&r=g");
});

it('can generate gravatar url with custom default image type', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = get_gravatar($email, 64, 'identicon');

    expect($url)->toBe("https://www.gravatar.com/avatar/{$hash}?s=64&d=identicon&r=g");
});

it('can generate gravatar url with force default', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = get_gravatar($email, 64, 'mp', true);

    expect($url)->toBe("https://www.gravatar.com/avatar/{$hash}?s=64&d=mp&r=g&f=y");
});

it('can generate gravatar url with custom rating', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = get_gravatar($email, 64, 'mp', false, 'pg');

    expect($url)->toBe("https://www.gravatar.com/avatar/{$hash}?s=64&d=mp&r=pg");
});

it('can return gravatar image tag', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = "https://www.gravatar.com/avatar/{$hash}?s=64&d=mp&r=g";
    $imageTag = get_gravatar($email, 64, 'mp', false, 'g', true);

    expect($imageTag)->toBe("<img src=\"{$url}\" />");
});

it('can return gravatar image tag with attributes', function () {
    $email = 'foo@bar.com';
    $hash = hash('sha256', strtolower(trim($email)));
    $url = "https://www.gravatar.com/avatar/{$hash}?s=64&d=mp&r=g";
    $imageTag = get_gravatar($email, 64, 'mp', false, 'g', true, ['class' => 'rounded', 'alt' => 'Avatar']);

    expect($imageTag)->toBe("<img src=\"{$url}\" class=\"rounded\" alt=\"Avatar\" />");
});

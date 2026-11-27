<?php

declare(strict_types=1);

use App\Actions\UploadImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\Image;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake());

it('converts an image to webp, stores it in the default directory, and returns its storage url', function () {
    $uploadedFile = UploadedFile::fake()->image('photo.jpg', 200, 200);
    $image = new Image(fn (): string => $uploadedFile->getContent(), $uploadedFile);

    $url = app(UploadImageAction::class)->handle($image);

    $storedFiles = Storage::disk()->files('images');
    $storedPath = $storedFiles[0] ?? null;

    expect($storedFiles)->toHaveCount(1)
        ->and($storedPath)->toEndWith('.webp')
        ->and(Storage::disk()->mimeType($storedPath))->toBe('image/webp')
        ->and($url)->toBe(Storage::disk()->url($storedPath));
});

it('stores the converted webp image in a custom directory', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.png', 150, 150);
    $image = new Image(fn (): string => $uploadedFile->getContent(), $uploadedFile);

    $url = app(UploadImageAction::class)->handle($image, 'avatars');

    $storedFiles = Storage::disk()->files('avatars');
    $storedPath = $storedFiles[0] ?? null;

    expect($storedFiles)->toHaveCount(1)
        ->and($storedPath)->toEndWith('.webp')
        ->and(Storage::disk()->mimeType($storedPath))->toBe('image/webp')
        ->and($url)->toBe(Storage::disk()->url($storedPath))
        ->and(Storage::disk()->files('images'))->toBeEmpty();
});

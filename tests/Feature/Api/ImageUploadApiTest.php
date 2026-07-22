<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\post;

beforeEach(fn() => Storage::fake());

describe('image upload api', function () {
    test('users who are not logged in cannot upload images', function () {
        post(route('images.store'), [
            'upload' => UploadedFile::fake()->image('photo.jpg')->size(100),
        ])
            ->assertStatus(302)
            ->assertRedirect(route('login'));

        Storage::disk()->assertDirectoryEmpty('images');
    });

    test('logged-in users can upload images as webp', function () {
        loginAsUser();

        $file = UploadedFile::fake()->image('photo.jpg')->size(100);

        $response = post(route('images.store'), [
            'upload' => $file,
        ])->assertSuccessful();

        $storedFiles = Storage::disk()->files('images');
        $storedPath = $storedFiles[0] ?? null;

        expect($storedFiles)->toHaveCount(1)
            ->and($storedPath)->toEndWith('.webp')
            ->and(Storage::disk()->mimeType($storedPath))->toBe('image/webp');

        $response->assertExactJson([
            'url' => Storage::disk()->url($storedPath),
        ]);
    });

    test('the upload field is required', function () {
        loginAsUser();

        post(route('images.store'), [])
            ->assertStatus(413)
            ->assertJsonStructure(['error' => ['message']]);

        Storage::disk()->assertDirectoryEmpty('images');
    });

    test('the width of the uploaded image must be less than 1200 px', function () {
        loginAsUser();

        post(route('images.store'), [
            'upload' => UploadedFile::fake()->image('photo.jpg', 1201, 100),
        ])
            ->assertStatus(413)
            ->assertJsonStructure(['error' => ['message']]);

        Storage::disk()->assertDirectoryEmpty('images');
    });

    test('the height of the uploaded image must be less than 1200 px', function () {
        loginAsUser();

        post(route('images.store'), [
            'upload' => UploadedFile::fake()->image('photo.jpg', 100, 1201),
        ])
            ->assertStatus(413)
            ->assertJsonStructure(['error' => ['message']]);

        Storage::disk()->assertDirectoryEmpty('images');
    });

    test('the size of the uploaded image must be less than 10MB', function () {
        loginAsUser();

        post(route('images.store'), [
            'upload' => UploadedFile::fake()->image('photo.jpg')->size(10 * 1024 + 1),
        ])
            ->assertStatus(413)
            ->assertJsonStructure(['error' => ['message']]);

        Storage::disk()->assertDirectoryEmpty('images');
    });

    test('the uploaded image must be an image', function () {
        loginAsUser();

        post(route('images.store'), [
            'upload' => UploadedFile::fake()->create('document.pdf', 100),
        ])
            ->assertStatus(413)
            ->assertJsonStructure(['error' => ['message']]);

        Storage::disk()->assertDirectoryEmpty('images');
    });
});

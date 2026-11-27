<?php

declare(strict_types=1);

use App\Actions\ClearUnusedImagesAction;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();

    $this->imageName1 = '2023_01_01_10_18_21_63b0ed6d06d52.jpg';
    $this->imageName2 = '2022_12_30_22_39_21_63aef81999216.jpg';
    $this->imageName3 = '2022_12_31_10_28_00_63af9e3067169.jpg';

    $content = UploadedFile::fake()->image('sample.jpg')->size(50)->getContent();

    Storage::disk()->put('images/'.$this->imageName1, $content);
    Storage::disk()->put('images/'.$this->imageName2, $content);
    Storage::disk()->put('images/'.$this->imageName3, $content);
});

it('deletes unused images by default when no callback is provided', function () {
    Post::factory()->create([
        'body' => '<div><img src="https://fake-url.com/images/'.$this->imageName1.'" alt="used"></div>',
        'cover_image_url' => 'https://fake-url.com/images/'.$this->imageName2,
    ]);

    $result = app(ClearUnusedImagesAction::class)->handle();

    expect($result)->toBe([
        'unused' => ['images/'.$this->imageName3],
        'deleted' => ['images/'.$this->imageName3],
    ]);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertMissing('images/'.$this->imageName3);
});

it('preserves unused images when the callback returns false', function () {
    Post::factory()->create([
        'body' => '<div><img src="https://fake-url.com/images/'.$this->imageName1.'" alt="used"></div>',
        'cover_image_url' => null,
    ]);

    $receivedUnused = [];

    $result = app(ClearUnusedImagesAction::class)->handle(function (array $unused) use (&$receivedUnused): bool {
        $receivedUnused = $unused;

        return false;
    });

    $expectedUnused = [
        'images/'.$this->imageName2,
        'images/'.$this->imageName3,
    ];

    expect($receivedUnused)->toEqualCanonicalizing($expectedUnused)
        ->and($result['unused'])->toEqualCanonicalizing($expectedUnused)
        ->and($result['deleted'])->toBe([]);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertExists('images/'.$this->imageName3);
});

it('deletes unused images when the callback returns true', function () {
    Post::factory()->create([
        'body' => '<div><img src="https://fake-url.com/images/'.$this->imageName1.'" alt="used"></div>',
        'cover_image_url' => 'https://fake-url.com/images/'.$this->imageName2,
    ]);

    $receivedUnused = [];

    $result = app(ClearUnusedImagesAction::class)->handle(function (array $unused) use (&$receivedUnused): bool {
        $receivedUnused = $unused;

        return true;
    });

    expect($receivedUnused)->toBe(['images/'.$this->imageName3])
        ->and($result)->toBe([
            'unused' => ['images/'.$this->imageName3],
            'deleted' => ['images/'.$this->imageName3],
        ]);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertMissing('images/'.$this->imageName3);
});

it('returns empty arrays and skips callback when there are no unused images', function () {
    Post::factory()->create([
        'body' => '<div><img src="https://fake-url.com/images/'.$this->imageName1.'"><img src="https://fake-url.com/images/'.$this->imageName2.'"></div>',
        'cover_image_url' => 'https://fake-url.com/images/'.$this->imageName3,
    ]);

    $callbackCalled = false;

    $result = app(ClearUnusedImagesAction::class)->handle(function () use (&$callbackCalled): bool {
        $callbackCalled = true;

        return true;
    });

    expect($callbackCalled)->toBeFalse()
        ->and($result)->toBe([
            'unused' => [],
            'deleted' => [],
        ]);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertExists('images/'.$this->imageName3);
});

<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('validates the upload field is required', function () {
    $response = actingAs($this->user)->postJson(route('images.store'), []);

    $response->assertStatus(413)
        ->assertJson([
            'error' => ['message' => 'Please select an image to upload.'],
        ]);
});

it('validates the upload is an image', function () {
    $file = UploadedFile::fake()->create('document.pdf', 500);

    $response = actingAs($this->user)->postJson(route('images.store'), [
        'upload' => $file,
    ]);

    $response->assertStatus(413)
        ->assertJson([
            'error' => ['message' => 'The uploaded file must be an image (JPEG, PNG, BMP, GIF, SVG, or WebP).'],
        ]);
});

it('validates the upload size is not more than 25MB', function () {
    $file = UploadedFile::fake()->image('large.jpg')->size(25 * 1024 + 1);

    $response = actingAs($this->user)->postJson(route('images.store'), [
        'upload' => $file,
    ]);

    $response->assertStatus(413)
        ->assertJson([
            'error' => ['message' => 'The image size cannot exceed 25MB.'],
        ]);
});

it('validates the upload dimensions', function () {
    $file = UploadedFile::fake()->image('wide.jpg', 1300, 100);

    $response = actingAs($this->user)->postJson(route('images.store'), [
        'upload' => $file,
    ]);

    $response->assertStatus(413)
        ->assertJson([
            'error' => ['message' => 'The image dimensions must not exceed 1200x1200 pixels.'],
        ]);
});

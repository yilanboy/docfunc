<?php

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// upload 3 fake image files to fake s3 storage
beforeEach(function () {
    Storage::fake();

    $this->imageName1 = '2023_01_01_10_18_21_63b0ed6d06d52.jpg';
    $this->imageName2 = '2022_12_30_22_39_21_63aef81999216.jpg';
    $this->imageName3 = '2022_12_31_10_28_00_63af9e3067169.jpg';

    // use getContent() here, so we can use storage put() to determine the filename
    $this->image1 = UploadedFile::fake()->image('image1.jpg')->size(100)->getContent();
    $this->image2 = UploadedFile::fake()->image('image2.jpg')->size(100)->getContent();
    $this->image3 = UploadedFile::fake()->image('image3.jpg')->size(100)->getContent();

    Storage::disk()->put('images/'.$this->imageName1, $this->image1);
    Storage::disk()->put('images/'.$this->imageName2, $this->image2);
    Storage::disk()->put('images/'.$this->imageName3, $this->image3);
});

it('can terminate the program by answering "no"', function () {
    $body = <<<EOF
    <div>
        <img src="https://fake-url.com/images/{$this->imageName1}" alt="{$this->imageName1}" title="" style="">
    </div>
    EOF;

    $post = Post::factory()->create(['body' => $body]);
    expect(Post::find($post->id))->body->toContain($this->imageName1);

    $this->artisan('image:clear')
        ->expectsOutput('This operation will delete unused images on S3')
        ->doesntExpectOutput('There is not a single image that has not been used')
        ->expectsConfirmation('Do you wish to continue?', 'no')
        ->expectsOutput('Stop this operation...')
        ->doesntExpectOutput('Clear operation finish')
        ->assertExitCode(1);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertExists('images/'.$this->imageName3);
});

it('can clear unused images', function () {
    $body = <<<EOF
    <div>
        <img src="https://fake-url.com/images/{$this->imageName1}" alt="{$this->imageName1}" title="" style="">
    </div>
    EOF;

    $post = Post::factory()->create([
        'body' => $body,
        'cover_image_url' => 'https://fake-url.com/images/'.$this->imageName2,
    ]);

    expect(Post::find($post->id))->body->toContain($this->imageName1);

    $this->artisan('image:clear')
        ->expectsOutput('This operation will delete unused images on S3')
        ->doesntExpectOutput('There is not a single image that has not been used')
        ->expectsConfirmation('Do you wish to continue?', 'yes')
        ->expectsOutput('Clear operation finish')
        ->doesntExpectOutput('Stop this operation...')
        ->assertExitCode(0);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertMissing('images/'.$this->imageName3);
});

it('can skip confirmed by adding --force flag', function () {
    $body = <<<EOF
    <div>
        <img src="https://fake-url.com/images/{$this->imageName1}" alt="{$this->imageName1}" title="" style="">
    </div>
    EOF;

    $post = Post::factory()->create(['body' => $body]);
    expect(Post::find($post->id))->body->toContain($this->imageName1);

    $this->artisan('image:clear --force')
        ->expectsOutput('This operation will delete unused images on S3')
        ->doesntExpectOutput('There is not a single image that has not been used')
        ->expectsOutput('Clear operation finish')
        ->doesntExpectOutput('Stop this operation...')
        ->assertExitCode(0);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertMissing('images/'.$this->imageName2)
        ->assertMissing('images/'.$this->imageName3);
});

test('if there are no unused images, the program will not delete any of them', function () {
    $body1 = <<<EOF
    <div>
        <img src="https://fake-url.com/images/{$this->imageName1}" alt="{$this->imageName1}" title="" style="">
    </div>
    EOF;

    $post1 = Post::factory()->create(['body' => $body1]);
    expect(Post::find($post1->id))->body->toContain($this->imageName1);

    $body2 = <<<EOF
    <div>
        <img src="https://fake-url.com/images/{$this->imageName2}" alt="{$this->imageName2}" title="" style="">
        <img src="https://fake-url.com/images/{$this->imageName3}" alt="{$this->imageName3}" title="" style="">
    </div>
    EOF;

    $post2 = Post::factory()->create(['body' => $body2]);
    expect(Post::find($post2->id))->body->toContain($this->imageName2);

    $this->artisan('image:clear')
        ->expectsOutput('There is not a single image that has not been used')
        ->assertExitCode(0);

    Storage::disk()
        ->assertExists('images/'.$this->imageName1)
        ->assertExists('images/'.$this->imageName2)
        ->assertExists('images/'.$this->imageName3);
});

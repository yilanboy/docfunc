<?php


use App\Models\Comment;
use App\Models\Post;

it('has comments', function () {
    $post = Post::factory()
        ->has(Comment::factory()->count(3))
        ->create();

    expect($post->comments)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Comment::class);
});

describe('isRevised', function () {
    it('returns false when post has not been updated', function () {
        $post = Post::factory()->create([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        expect($post->isRevised())->toBeFalse();
    });

    it('returns false when post is updated on the same day within 24 hours', function () {
        $post = Post::factory()->create([
            'created_at' => now()->startOfDay()->addHours(10),
            'updated_at' => now()->startOfDay()->addHours(12),
        ]);

        expect($post->isRevised())->toBeFalse();
    });

    it('returns false when post crosses midnight but is within 24 hours', function () {
        $created = now()->startOfDay()->addHours(23)->addMinutes(55);
        $updated = $created->copy()->addMinutes(10); // next day, but only 10 mins apart

        $post = Post::factory()->create([
            'created_at' => $created,
            'updated_at' => $updated,
        ]);

        expect($post->isRevised())->toBeFalse();
    });

    it('returns true when post is updated after 24 hours on a different date', function () {
        $created = now()->subDays(3);
        $updated = now()->subDay();

        $post = Post::factory()->create([
            'created_at' => $created,
            'updated_at' => $updated,
        ]);

        expect($post->isRevised())->toBeTrue();
    });
});


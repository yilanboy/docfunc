<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Services\ContentService;
use Generator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;
use Random\RandomException;

class PostSeeder extends Seeder
{
    const int POST_COUNT = 1_000;

    const int CHUNK = 100;

    /**
     * @throws BindingResolutionException
     * @throws RandomException
     */
    protected function postGenerator(int $userCount): Generator
    {
        $contentService = app()->make(ContentService::class);

        for ($i = 1; $i <= self::POST_COUNT; $i++) {
            $title = fake()->text(30);
            $body = fake()->paragraph(50);
            $excerpt = $contentService->getExcerpt($body);
            $slug = $contentService->getSlug($title);

            $data[] = [
                'title'       => $title,
                'body'        => $body,
                'slug'        => $slug,
                'excerpt'     => $excerpt,
                'category_id' => fake()->numberBetween(1, 3),
                'user_id'     => random_int(1, $userCount),
                'created_at'  => fake()->dateTimeBetween(startDate: '-3 years'),
                'updated_at'  => now(),
            ];

            if ($i % self::CHUNK === 0) {
                yield $data;

                $data = [];
            }
        }
    }

    public function run(): void
    {
        $userCount = User::query()->count();

        foreach ($this->postGenerator($userCount) as $data) {
            Post::query()->insert($data);
        }
    }
}

<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * @extends Factory<Post>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(30),
            'body' => fake()->paragraph(50),
            'is_private' => false,
            'cover_image_url' => fake()->imageUrl(),
            'slug' => fake()->word(),
            'excerpt' => fake()->sentence,
            'category_id' => fake()->numberBetween(1, 3),
            'user_id' => User::factory(),
            // 隨機取一個月以內，但早於現在的時間
            'created_at' => fake()->dateTimeThisMonth(now()),
            'updated_at' => now(),
        ];
    }

    public function userId(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
    }
}

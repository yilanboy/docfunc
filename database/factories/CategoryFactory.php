<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'icon' => $this->faker->bothify('##### #####'),
            'description' => $this->faker->sentence(),
            'is_default' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->unique()->bothify('??-####')),
            'price' => fake()->randomFloat(2, 1, 999),
            'stock' => fake()->numberBetween(0, 100),
            'description' => fake()->sentence(10),
        ];
    }
}

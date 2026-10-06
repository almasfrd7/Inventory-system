<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => fake()->unique()->text(8),
            'price' => fake()->randomFloat(2, 1, 999),
            'stock' => fake()->numberBetween(0, 100),
            'description' => fake()->sentence(10),
        ];
    }
}
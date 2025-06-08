<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku'         => 'PRD-' . strtoupper($this->faker->unique()->bothify('####')),
            'name'        => $this->faker->word . ' Product',
            'category_id' => Category::factory(),
            'description' => $this->faker->sentence,
            'unit'        => $this->faker->randomElement(['pcs', 'kg', 'box']),
            'created_by'  => User::factory(),
        ];
    }
}
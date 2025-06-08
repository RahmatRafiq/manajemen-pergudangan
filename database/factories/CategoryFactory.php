<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['finished_goods', 'raw_material', 'consumable'];

        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->sentence(),
        ];
    }
}
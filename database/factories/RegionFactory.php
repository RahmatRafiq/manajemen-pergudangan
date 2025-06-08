<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => strtoupper($this->faker->unique()->bothify('RGN###')),
            'name'        => $this->faker->city,
            'description' => $this->faker->sentence,
        ];
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'region_id' => 1, // atau ganti sesuai kebutuhan test
            'reference' => 'WH-' . strtoupper($this->faker->unique()->bothify('########')),
            'name'      => $this->faker->company . ' Warehouse',
            'address'   => $this->faker->address,
            'phone'     => $this->faker->phoneNumber,
            'manager'   => $this->faker->name,
        ];
    }
}
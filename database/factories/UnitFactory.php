<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => \App\Models\Community::factory(),
            'identifier' => 'Apto ' . $this->faker->unique()->numberBetween(100, 9999),
            'type' => $this->faker->randomElement(['apartment', 'house', 'local', 'parking', 'storage']),
            'status' => $this->faker->randomElement(['occupied', 'vacant', 'maintenance']),
        ];
    }
}

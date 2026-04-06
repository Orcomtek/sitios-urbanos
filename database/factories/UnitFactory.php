<?php

namespace Database\Factories;

use App\Models\Community;
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
            'community_id' => Community::factory(),
            'identifier' => 'Apto '.$this->faker->unique()->numberBetween(100, 9999),
            'property_type' => $this->faker->randomElement(['apartment', 'house', 'commercial', 'office', 'warehouse']),
            'status' => $this->faker->randomElement(['occupied', 'available', 'maintenance']),
            'has_parking' => false,
            'has_storage' => false,
        ];
    }
}

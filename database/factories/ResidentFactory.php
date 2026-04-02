<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resident>
 */
class ResidentFactory extends Factory
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
            'unit_id' => Unit::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['tenant', 'owner']),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}

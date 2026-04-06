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
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'resident_type' => fake()->randomElement(['tenant', 'owner']),
            'is_active' => fake()->boolean(80),
            'pays_administration' => fake()->boolean(50),
        ];
    }
}

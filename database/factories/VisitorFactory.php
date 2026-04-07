<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
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
            'created_by' => User::factory(),
            'name' => fake()->name(),
            'document_number' => fake()->numerify('##########'),
            'type' => fake()->randomElement(['visitor', 'delivery', 'service', 'other']),
            'status' => 'pending',
            'expected_at' => fake()->dateTimeBetween('now', '+1 week'),
            'notes' => fake()->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmergencyEvent>
 */
class EmergencyEventFactory extends Factory
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
            'triggered_by' => User::factory(),
            'type' => 'panic',
            'status' => 'active',
            'description' => $this->faker->sentence(),
            'triggered_at' => now(),
            'acknowledged_at' => null,
            'resolved_at' => null,
            'notes' => null,
        ];
    }
}

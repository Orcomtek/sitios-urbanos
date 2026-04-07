<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
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
            'received_by' => User::factory(),
            'carrier' => $this->faker->company(),
            'tracking_number' => $this->faker->uuid(),
            'sender_name' => $this->faker->name(),
            'recipient_name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'status' => 'received',
            'received_at' => now(),
        ];
    }
}

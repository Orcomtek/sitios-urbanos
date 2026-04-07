<?php

namespace Database\Factories;

use App\Enums\PqrsStatus;
use App\Enums\PqrsType;
use App\Models\Community;
use App\Models\Pqrs;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pqrs>
 */
class PqrsFactory extends Factory
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
            'resident_id' => Resident::factory(),
            'type' => $this->faker->randomElement([
                PqrsType::PETITION,
                PqrsType::COMPLAINT,
                PqrsType::CLAIM,
                PqrsType::SUGGESTION,
            ]),
            'status' => PqrsStatus::OPEN,
            'subject' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_anonymous' => false,
            'admin_response' => null,
            'responded_at' => null,
            'closed_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Enums\ServiceUrgency;
use App\Models\Community;
use App\Models\Provider;
use App\Models\ProviderServiceRequest;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderServiceRequest>
 */
class ProviderServiceRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProviderServiceRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'resident_id' => function (array $attributes) {
                return Resident::factory()->create([
                    'community_id' => $attributes['community_id'] ?? Community::factory(),
                ])->id;
            },
            'provider_id' => function (array $attributes) {
                return Provider::factory()->create([
                    'community_id' => $attributes['community_id'] ?? Community::factory(),
                ])->id;
            },
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(ServiceRequestStatus::cases()),
            'urgency' => $this->faker->randomElement(ServiceUrgency::cases()),
            'scheduled_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }

    /**
     * Indicate that the status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::PENDING,
        ]);
    }
}

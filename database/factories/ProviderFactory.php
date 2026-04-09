<?php

namespace Database\Factories;

use App\Enums\ProviderCategory;
use App\Enums\ProviderStatus;
use App\Models\Community;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(ProviderCategory::cases())->value,
            'contact_details' => [
                ['type' => 'phone', 'value' => fake()->phoneNumber()],
                ['type' => 'email', 'value' => fake()->safeEmail()],
            ],
            'status' => ProviderStatus::ACTIVE->value,
            'is_recommended' => fake()->boolean(20),
        ];
    }
}

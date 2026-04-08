<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ListingCategory;
use App\Enums\ListingStatus;
use App\Models\Community;
use App\Models\Listing;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'resident_id' => Resident::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(1000, 500000),
            'category' => fake()->randomElement(ListingCategory::cases()),
            'status' => ListingStatus::Active,
            'show_contact_info' => fake()->boolean(),
        ];
    }
}

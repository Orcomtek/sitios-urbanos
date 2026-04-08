<?php

declare(strict_types=1);

namespace App\Actions\Ecosystem;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Resident;

class CreateListingAction
{
    public function execute(Resident $resident, array $data): Listing
    {
        return Listing::create([
            'resident_id' => $resident->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'] ?? null,
            'category' => $data['category'],
            'status' => ListingStatus::Active,
            'show_contact_info' => $data['show_contact_info'] ?? false,
        ]);
    }
}

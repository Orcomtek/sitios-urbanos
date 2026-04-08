<?php

declare(strict_types=1);

namespace App\Actions\Ecosystem;

use App\Models\Listing;
use App\Models\Resident;

class UpdateListingAction
{
    public function execute(Resident $resident, Listing $listing, array $data): Listing
    {
        if ($listing->resident_id !== $resident->id) {
            abort(403, 'Unauthorized to update this listing.');
        }

        $listing->update([
            'title' => $data['title'] ?? $listing->title,
            'description' => $data['description'] ?? $listing->description,
            'price' => array_key_exists('price', $data) ? $data['price'] : $listing->price,
            'category' => $data['category'] ?? $listing->category,
            'status' => $data['status'] ?? $listing->status,
            'show_contact_info' => $data['show_contact_info'] ?? $listing->show_contact_info,
        ]);

        return $listing->refresh();
    }
}

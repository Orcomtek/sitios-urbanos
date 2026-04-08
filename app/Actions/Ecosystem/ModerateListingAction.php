<?php

declare(strict_types=1);

namespace App\Actions\Ecosystem;

use App\Enums\ListingStatus;
use App\Models\Listing;

class ModerateListingAction
{
    public function execute(Listing $listing, ListingStatus $status): Listing
    {
        $listing->update([
            'status' => $status,
        ]);

        return $listing;
    }
}

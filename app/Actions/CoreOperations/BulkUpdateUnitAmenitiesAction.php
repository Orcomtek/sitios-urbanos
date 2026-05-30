<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;

class BulkUpdateUnitAmenitiesAction
{
    /**
     * Bulk update amenities for the given units by merging the new amenities with the existing ones.
     * Ensure strict tenant isolation.
     */
    public function execute(Community $community, array $unitIds, array $newAmenities): void
    {
        if (empty($unitIds) || empty($newAmenities)) {
            return;
        }

        // Fetch the units ensuring they belong to the current community
        $units = $community->units()->whereIn('id', $unitIds)->get();

        foreach ($units as $unit) {
            $existingAmenities = $unit->amenities ?? [];

            // Merge existing and new amenities
            $mergedAmenities = array_merge($existingAmenities, $newAmenities);

            // Remove duplicates and reset array keys
            $uniqueAmenities = array_values(array_unique($mergedAmenities));

            $unit->update([
                'amenities' => $uniqueAmenities,
            ]);
        }
    }
}

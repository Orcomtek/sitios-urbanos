<?php

namespace App\Actions\CoreOperations;

use App\Models\Community;
use App\Models\Unit;

class GenerateTopologicalMatrixAction
{
    /**
     * Generate topological units based on a template string and insert them, ignoring duplicates.
     *
     * @param  array{blocks: int, floors: int, units: int, pattern: string, property_type?: string}  $data
     */
    public function execute(Community $community, array $data): void
    {
        $blocks = max(1, (int) ($data['blocks'] ?? 1));
        $floors = max(1, (int) ($data['floors'] ?? 1));
        $unitsPerFloor = max(1, (int) ($data['units'] ?? 1));
        $pattern = $data['pattern'] ?? '{B}-{P}{U}';
        $propertyType = $data['property_type'] ?? 'apartment';

        $unitsToInsert = [];
        $now = now();

        for ($b = 1; $b <= $blocks; $b++) {
            for ($p = 1; $p <= $floors; $p++) {
                for ($u = 1; $u <= $unitsPerFloor; $u++) {
                    $paddedU = str_pad((string) $u, 2, '0', STR_PAD_LEFT);

                    // The dynamic template string parser replaces {B}, {P}, and {U}
                    $identifier = str_replace(
                        ['{B}', '{P}', '{U}'],
                        [$b, $p, $paddedU],
                        $pattern
                    );

                    $unitsToInsert[] = [
                        'community_id' => $community->id,
                        'identifier' => $identifier,
                        'property_type' => $propertyType,
                        'status' => 'occupied', // Default status from migration
                        'amenities' => json_encode([]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Chunk inserts to avoid query limits on huge generations
        foreach (array_chunk($unitsToInsert, 500) as $chunk) {
            Unit::insertOrIgnore($chunk);
        }
    }
}

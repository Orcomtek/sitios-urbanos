<?php

use App\Models\Community;
use App\Models\Listing;
use App\Models\Resident;
use App\Enums\ListingStatus;
use App\Enums\ListingCategory;

$community = Community::where('slug', 'test-community')->first();
if (!$community) {
    echo "Error: Community 'test-community' not found.\n";
    exit(1);
}

$resident = Resident::where('community_id', $community->id)->first();
if (!$resident) {
    echo "Error: No resident found in 'test-community'.\n";
    exit(1);
}

Listing::create([
    'community_id' => $community->id,
    'resident_id' => $resident->id,
    'title' => 'Venta de Bicicleta Usada',
    'description' => 'Perfecto estado, poco uso.',
    'price' => 150000,
    'category' => ListingCategory::Items->value,
    'status' => ListingStatus::Active->value,
    'show_contact_info' => true,
]);

Listing::create([
    'community_id' => $community->id,
    'resident_id' => $resident->id,
    'title' => 'Servicio de Jardinería Profesional',
    'description' => 'Mantenimiento de jardines y plantas interiores.',
    'price' => 50000,
    'category' => ListingCategory::Services->value,
    'status' => ListingStatus::Active->value,
    'show_contact_info' => true,
]);

echo "Successfully created 2 active listings for testing Admin Queue.\n";

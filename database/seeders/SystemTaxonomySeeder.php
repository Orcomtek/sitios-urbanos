<?php

namespace Database\Seeders;

use App\Models\SystemTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemTaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['label' => 'Plomería', 'value' => 'plumbing'],
            ['label' => 'Electricidad', 'value' => 'electrical'],
            ['label' => 'Limpieza', 'value' => 'cleaning'],
            ['label' => 'Mantenimiento', 'value' => 'maintenance'],
            ['label' => 'Otros', 'value' => 'other'],
        ];

        foreach ($categories as $category) {
            SystemTaxonomy::firstOrCreate([
                'type' => 'provider_category',
                'value' => $category['value'],
                'community_id' => null,
            ], [
                'id' => Str::uuid(),
                'label' => $category['label'],
                'is_active' => true,
            ]);
        }
    }
}

<?php

use App\Models\Community;
use App\Models\User;
use App\Models\Unit;
use App\Models\Resident;
use Illuminate\Support\Facades\Hash;
use App\Enums\CommunityRole;

echo "Starting local Cockpit test setup...\n\n";

// 1. Ensure the Community exists
$community = Community::firstOrCreate(
    ['slug' => 'test-community'],
    ['name' => 'Comunidad de Pruebas']
);

// 2. Create the Admin User
$admin = User::firstOrCreate(
    ['email' => 'admin@test.com'],
    ['name' => 'Cockpit Admin', 'password' => Hash::make('password123')]
);
$community->users()->syncWithoutDetaching([$admin->id => ['role' => CommunityRole::Admin->value]]);
echo "✅ Admin Created: admin@test.com\n";

// 3. Create the Guard User
$guard = User::firstOrCreate(
    ['email' => 'guard@test.com'],
    ['name' => 'Cockpit Guard', 'password' => Hash::make('password123')]
);
$community->users()->syncWithoutDetaching([$guard->id => ['role' => CommunityRole::Guard->value]]);
echo "✅ Guard Created: guard@test.com\n";

// 4. Create the Resident User
$residentUser = User::firstOrCreate(
    ['email' => 'resident@test.com'],
    ['name' => 'Cockpit Resident', 'password' => Hash::make('password123')]
);
$community->users()->syncWithoutDetaching([$residentUser->id => ['role' => CommunityRole::Resident->value]]);

// 5. Link the Resident to a Unit within the community
$unit = $community->units()->firstOrCreate([
    'identifier' => 'Apto 101'
]);

$community->residents()->firstOrCreate([
    'email' => 'resident@test.com'
], [
    'unit_id' => $unit->id,
    'user_id' => $residentUser->id,
    'full_name' => 'Cockpit Resident',
    'phone' => '3001234567',
    'resident_type' => 'owner',
    'is_active' => true,
    'pays_administration' => true,
]);
echo "✅ Resident Created: resident@test.com (Linked to {$unit->identifier})\n\n";

$centralDomain = config('app.central_domain', 'sitios-urbanos.test');

echo "========================================================\n";
echo "🔐 CREDENTIALS (All passwords are 'password123')\n";
echo "1. Admin: admin@test.com\n";
echo "2. Guard: guard@test.com\n";
echo "3. Resident: resident@test.com\n";
echo "========================================================\n\n";

echo "🔗 URLS TO TEST (ensure 'test-community' is locally resolvable or active in Herd):\n\n";

echo "Base Global App (Login here first): \n";
echo "http://app.sitios-urbanos.test/login\n\n";

echo "Tenant Cockpit URLs:\n";
echo "- Dashboard: http://test-community.app.sitios-urbanos.test/cockpit/dashboard\n";
echo "- Work Queue: http://test-community.app.sitios-urbanos.test/cockpit/work-queue\n";
echo "- Admin Queue: http://test-community.app.sitios-urbanos.test/cockpit/admin-work-queue\n";
echo "========================================================\n";

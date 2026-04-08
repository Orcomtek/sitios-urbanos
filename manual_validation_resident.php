<?php

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\AccessInvitation;
use App\Models\Pqrs;
use App\Models\Visitor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$communitySlug = 'eum-quidem-qui-quos-dolor';
$community = Community::where('slug', $communitySlug)->first();

app(\App\Services\TenantContext::class)->set($community);

$email = 'resident.test.' . Str::random(4) . '@sitios-urbanos.test';
$password = 'password';

$unit = Unit::firstOrCreate(
    ['community_id' => $community->id, 'identifier' => 'APT-RES-2'],
    ['property_type' => 'apartment', 'community_id' => $community->id]
);

$user = User::factory()->create([
    'name' => 'Demo Resident 2',
    'email' => $email,
    'password' => Hash::make($password),
]);

$user->communities()->attach($community->id, ['role' => CommunityRole::Resident->value]);

$resident = Resident::create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'user_id' => $user->id,
    'full_name' => 'Demo Resident 2',
    'email' => $email,
    'phone' => '1234567890',
    'resident_type' => 'tenant',
    'is_active' => true,
]);

// 1. Give some pending invoices
Invoice::create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'status' => 'pending',
    'amount' => 150000,
    'type' => 'admin_fee',
    'description' => 'Cuota de Administración',
    'issued_at' => now(),
    'due_date' => now()->addDays(5),
]);

// 2. Packages
Package::create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'status' => 'received',
    'carrier' => 'Servientrega',
    'recipient_name' => 'Demo Resident 2',
]);

// 3. Invitations
AccessInvitation::create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'status' => 'active',
    'guest_name' => 'Maria Gonzalez',
    'guest_document' => '1234567',
    'code' => 'TEST1234',
    'expires_at' => now()->addDays(2),
]);

// 4. PQRS
Pqrs::create([
    'community_id' => $community->id,
    'resident_id' => $resident->id,
    'status' => 'open',
    'subject' => 'Ruido en la madrugada',
    'content' => 'Se escucha mucho ruido.',
    'type' => 'complaint',
]);

// 5. Visitors
Visitor::create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'status' => 'pending', 
    'name' => 'Diana Perez',
    'document_number' => '7654322',
]);

echo "Success!\n";
echo "Community: {$community->slug}\n";
echo "URL: http://{$community->slug}.app.sitios-urbanos.test/cockpit/resident\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n";

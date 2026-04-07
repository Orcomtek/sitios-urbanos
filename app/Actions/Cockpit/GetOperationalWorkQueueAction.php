<?php

namespace App\Actions\Cockpit;

use App\Enums\CommunityRole;
use App\Models\AccessInvitation;
use App\Models\EmergencyEvent;
use App\Models\Package;
use App\Models\Visitor;

class GetOperationalWorkQueueAction
{
    public function execute(CommunityRole $role): array
    {
        $limit = 10;
        $tasks = [];

        // A. Visitors: pending -> action: enter
        $visitors = Visitor::where('status', 'pending')
            ->with(['unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($visitors as $visitor) {
            $tasks[] = collect([
                'id' => $visitor->id,
                'type' => 'visitor_pending',
                'unit' => $visitor->unit ? ['id' => $visitor->unit->id, 'unit_number' => $visitor->unit->unit_number] : null,
                'label' => 'Visitor: ' . $visitor->name,
                'action' => 'enter',
                'created_at' => $visitor->created_at->toIso8601String(),
            ]);
        }

        // B. Packages: received -> action: deliver
        $packages = Package::where('status', 'received')
            ->with(['unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($packages as $package) {
            $tasks[] = collect([
                'id' => $package->id,
                'type' => 'package_received',
                'unit' => $package->unit ? ['id' => $package->unit->id, 'unit_number' => $package->unit->unit_number] : null,
                'label' => 'Package for ' . ($package->recipient_name ?: ($package->unit?->unit_number ?? 'Unknown Unit')),
                'action' => 'deliver',
                'created_at' => $package->created_at->toIso8601String(),
            ]);
        }

        // C. Invitations: active and not expired -> action: validate
        $invitations = AccessInvitation::where('status', 'active')
            ->where('expires_at', '>', now())
            ->with(['unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($invitations as $invitation) {
            $tasks[] = collect([
                'id' => $invitation->id,
                'type' => 'invitation_active',
                'unit' => $invitation->unit ? ['id' => $invitation->unit->id, 'unit_number' => $invitation->unit->unit_number] : null,
                'label' => 'Invitation ' . $invitation->code,
                'action' => 'consume',
                'created_at' => $invitation->created_at->toIso8601String(),
            ]);
        }

        // D. Emergencies: active -> action: acknowledge
        $emergencies = EmergencyEvent::where('status', 'active')
            ->with(['unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($emergencies as $emergency) {
            $tasks[] = collect([
                'id' => $emergency->id,
                'type' => 'emergency_active',
                'unit' => $emergency->unit ? ['id' => $emergency->unit->id, 'unit_number' => $emergency->unit->unit_number] : null,
                'label' => 'Emergency: ' . ucfirst($emergency->type),
                'action' => 'acknowledge',
                'created_at' => $emergency->created_at->toIso8601String(),
            ]);
        }

        $sortedTasks = collect($tasks)->sortBy('created_at')->values()->map(function ($task) {
            return $task->except('created_at')->toArray();
        })->toArray();

        return [
            'role' => $role->value,
            'generated_at' => now()->toIso8601String(),
            'tasks' => $sortedTasks,
        ];
    }
}

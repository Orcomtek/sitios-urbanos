<?php

namespace App\Actions\Cockpit;

use App\Enums\CommunityRole;
use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Governance\Announcement;
use App\Models\Governance\Poll;
use App\Models\Invoice;
use App\Models\Pqrs;

class GetAdminWorkQueueAction
{
    public function execute(CommunityRole $role): array
    {
        $limit = 10;
        $tasks = [];

        // A. PQRS: open/in_progress -> action: respond
        $pqrsList = Pqrs::whereIn('status', ['open', 'in_progress'])
            ->with(['resident.unit:id,unit_number', 'resident:id,full_name,unit_id'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($pqrsList as $pqrs) {
            $unit = $pqrs->resident?->unit;
            $tasks[] = collect([
                'id' => $pqrs->id,
                'type' => 'pqrs_open',
                'unit' => $unit ? ['id' => $unit->id, 'unit_number' => $unit->unit_number] : null,
                'label' => 'PQRS: '.$pqrs->subject,
                'action' => 'respond',
                'created_at' => $pqrs->created_at->toIso8601String(),
            ]);
        }

        // B. Polls: open -> action: review
        $polls = Poll::where('status', 'open')
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($polls as $poll) {
            $tasks[] = collect([
                'id' => $poll->id,
                'type' => 'poll_active',
                'unit' => null,
                'label' => 'Poll: '.$poll->title,
                'action' => 'review',
                'created_at' => $poll->created_at->toIso8601String(),
            ]);
        }

        // C. Invoices: pending -> action: review
        $invoices = Invoice::where('status', 'pending')
            ->with(['unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($invoices as $invoice) {
            $tasks[] = collect([
                'id' => $invoice->id,
                'type' => 'invoice_pending',
                'unit' => $invoice->unit ? ['id' => $invoice->unit->id, 'unit_number' => $invoice->unit->unit_number] : null,
                'label' => 'Invoice: '.$invoice->description,
                'action' => 'review',
                'created_at' => $invoice->created_at->toIso8601String(),
            ]);
        }

        // D. Announcements: active (starts_at <= now, ends_at > now or null) -> action: view
        $announcements = Announcement::where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($announcements as $announcement) {
            $tasks[] = collect([
                'id' => $announcement->id,
                'type' => 'announcement_active',
                'unit' => null,
                'label' => 'Announcement: '.$announcement->title,
                'action' => 'view',
                'created_at' => $announcement->created_at->toIso8601String(),
            ]);
        }

        // E. Listings: active -> action: moderate
        $listings = Listing::where('status', ListingStatus::Active->value)
            ->with(['resident:id,full_name,unit_id', 'resident.unit:id,unit_number'])
            ->oldest()
            ->take($limit)
            ->get();

        foreach ($listings as $listing) {
            $unit = $listing->resident?->unit;
            $tasks[] = collect([
                'id' => $listing->id,
                'type' => 'listing_active',
                'unit' => $unit ? ['id' => $unit->id, 'unit_number' => $unit->unit_number] : null,
                'label' => 'Anuncio: '.$listing->title,
                'action' => 'moderate',
                'created_at' => $listing->created_at->toIso8601String(),
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

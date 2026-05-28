<?php

namespace App\Http\Controllers\Tenant\Resident\Governance;

use App\Http\Controllers\Controller;
use App\Models\Governance\Document;
use App\Models\Governance\Poll;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PollController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = app(TenantContext::class)->get()->id;
        $userId = $request->user()->id;

        // Eager load ONLY pending active items 
        $activePolls = Poll::with(['options'])
            ->where('community_id', $tenantId)
            ->where('status', 'active')
            ->whereDoesntHave('votes', function ($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)
                      ->where('community_id', $tenantId);
            })
            ->latest()
            ->get();

        $pendingDocuments = Document::where('community_id', $tenantId)
            ->whereDoesntHave('signatures', function ($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)
                      ->where('community_id', $tenantId);
            })
            ->latest()
            ->get();

        return Inertia::render('Tenant/Resident/Governance/Polls/Index', [
            'activePolls' => $activePolls,
            'pendingDocuments' => $pendingDocuments,
        ]);
    }
}

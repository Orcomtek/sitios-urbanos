<?php

namespace App\Actions\Governance;

use App\Models\Governance\Poll;
use App\Models\Governance\PollVote;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SubmitPollVoteAction
{
    public function handle(Request $request, Poll $poll)
    {
        Gate::authorize('vote', $poll);

        $validated = $request->validate([
            'poll_option_id' => ['required', 'exists:poll_options,id'],
            'unit_id' => ['required', 'exists:units,id'],
        ]);

        $tenantId = app(TenantContext::class)->get()->id;

        if (! $poll->options()->where('id', $validated['poll_option_id'])->exists()) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Opción de votación inválida para esta asamblea.');
        }

        PollVote::create([
            'community_id' => $tenantId,
            'poll_id' => $poll->id,
            'poll_option_id' => $validated['poll_option_id'],
            'user_id' => $request->user()->id,
            'unit_id' => $validated['unit_id'],
            'vote_weight' => 1.00, // Prepared for LATAM Property Coefficients
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Tu voto ha sido registrado exitosamente y bloqueado para auditoría.');
    }
}

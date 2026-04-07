<?php

namespace App\Http\Controllers\Api\Governance;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Governance\PollResource;
use App\Models\Governance\Poll;
use App\Models\Governance\PollVote;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();

        $polls = Poll::where('community_id', $community->id)
            ->with(['options' => function ($q) {
                // Return options with vote counts
                $q->withCount('votes');
            }])
            ->latest()
            ->paginate(50);

        return response()->json(PollResource::collection($polls)->response()->getData(true));
    }

    public function store(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden crear votaciones.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:informative,binding',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $poll = Poll::create([
                'community_id' => $community->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'] ?? 'informative',
                'status' => 'open',
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($validated['options'] as $optionText) {
                $poll->options()->create(['text' => $optionText]);
            }

            DB::commit();

            $poll->load('options');

            return response()->json([
                'message' => 'Poll created successfully',
                'data' => new PollResource($poll),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Request $request, string $community_slug, string $poll_id): JsonResponse
    {
        $community = $this->context->require();
        $poll = Poll::where('community_id', $community->id)
            ->with(['options' => function ($q) {
                $q->withCount('votes');
            }])
            ->findOrFail($poll_id);

        return response()->json([
            'data' => new PollResource($poll),
        ]);
    }

    public function close(Request $request, string $community_slug, string $poll_id): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden cerrar votaciones.');
        }

        $poll = Poll::where('community_id', $community->id)->findOrFail($poll_id);
        $poll->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Poll closed successfully',
            'data' => new PollResource($poll->load('options')),
        ]);
    }

    public function vote(Request $request, string $community_slug, string $poll_id): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        $validated = $request->validate([
            'unit_id' => 'required|integer',
            'poll_option_id' => 'required|integer',
        ]);

        $poll = Poll::where('community_id', $community->id)->findOrFail($poll_id);

        if (! $poll->isOpen()) {
            throw ValidationException::withMessages(['poll' => ['Esta votación no está abierta para recibir votos.']]);
        }

        // Verify that the requested poll_option belongs to this poll
        $option = $poll->options()->where('id', $validated['poll_option_id'])->first();
        if (! $option) {
            throw ValidationException::withMessages(['poll_option_id' => ['La opción seleccionada no pertenece a esta votación.']]);
        }

        // Verify the user is linked to the unit in the current community and is active.
        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('unit_id', $validated['unit_id'])
            ->where('is_active', true)
            ->first();

        if (! $resident) {
            abort(403, 'No estás autorizado para votar en representación de esta unidad.');
        }

        // Enforce Voting constraint: Check if unit has already voted in this poll
        $existingVote = PollVote::where('poll_id', $poll->id)
            ->where('unit_id', $validated['unit_id'])
            ->exists();

        if ($existingVote) {
            throw ValidationException::withMessages(['unit_id' => ['Esta unidad ya ha registrado su voto para esta votación.']]);
        }

        // Create the vote safely
        $vote = PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $user->id,
            'unit_id' => $validated['unit_id'],
        ]);

        return response()->json([
            'message' => 'Voto registrado exitosamente',
        ]);
    }
}

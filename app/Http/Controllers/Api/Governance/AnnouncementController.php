<?php

namespace App\Http\Controllers\Api\Governance;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Governance\AnnouncementResource;
use App\Models\Governance\Announcement;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();

        // All residents and admins can view announcements, provided they are active today
        // We will return all announcements for the tenant. The UI can filter if needed.
        $announcements = Announcement::where('community_id', $community->id)
            ->latest()
            ->paginate(50);

        return response()->json(AnnouncementResource::collection($announcements)->response()->getData(true));
    }

    public function store(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden crear anuncios.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:informational,urgent,operational,institutional',
            'target_audience' => 'nullable|array',
            'attachment_url' => 'nullable|url|max:2048',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement = Announcement::create(array_merge($validated, [
            'community_id' => $community->id,
            'created_by' => $user->id,
        ]));

        return response()->json([
            'message' => 'Announcement created successfully',
            'data' => new AnnouncementResource($announcement),
        ], 201);
    }
    
    public function destroy(Request $request, string $community_slug, Announcement $announcement): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden eliminar anuncios.');
        }

        if ($announcement->community_id !== $community->id) {
            abort(404);
        }

        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ]);
    }
}

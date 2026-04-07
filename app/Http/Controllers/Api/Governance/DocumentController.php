<?php

namespace App\Http\Controllers\Api\Governance;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Governance\DocumentResource;
use App\Models\Governance\Document;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();

        $documents = Document::where('community_id', $community->id)
            ->latest()
            ->paginate(50);

        return response()->json(DocumentResource::collection($documents)->response()->getData(true));
    }

    public function store(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden subir documentos.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'nullable|string|in:regulation,minutes,certificate,communication,administrative',
            'file_url' => 'required|url|max:2048',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string|max:100',
            'visibility' => 'nullable|array',
        ]);

        $document = Document::create(array_merge($validated, [
            'community_id' => $community->id,
            'created_by' => $user->id,
        ]));

        return response()->json([
            'message' => 'Document created successfully',
            'data' => new DocumentResource($document),
        ], 201);
    }

    public function destroy(Request $request, string $community_slug, Document $document): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden eliminar documentos.');
        }

        if ($document->community_id !== $community->id) {
            abort(404);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }
}

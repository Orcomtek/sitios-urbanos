<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Actions\Cockpit\GetActivityTimelineAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityTimelineResource;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityTimelineController extends Controller
{
    public function __construct(
        protected TenantContext $context,
        protected GetActivityTimelineAction $getAction
    ) {}

    public function index(Request $request): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        $items = $this->getAction->execute($user, $community, 20);

        return response()->json([
            'data' => ActivityTimelineResource::collection($items),
        ]);
    }
}

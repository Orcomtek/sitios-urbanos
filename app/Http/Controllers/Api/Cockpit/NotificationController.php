<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        // Load notifications for the user scoped to the current community.
        // We use the JSON arrow operator on PostgreSQL 'data' column.
        $notifications = $user->notifications()
            ->where('data->community_id', (string) $community->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $community_slug, string $id): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        $notification = $user->notifications()
            ->where('data->community_id', (string) $community->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }

    public function markAllAsRead(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        $user->unreadNotifications()
            ->where('data->community_id', (string) $community->id)
            ->get()
            ->markAsRead();

        return response()->json([
            'message' => 'All visible notifications marked as read.',
        ]);
    }
}

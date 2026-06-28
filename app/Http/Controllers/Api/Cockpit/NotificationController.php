<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
            ->where('data->community_id', $community->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    public function read(Request $request, string $community_slug, string $id): RedirectResponse
    {
        $community = $this->context->require();

        $notification = $request->user()->notifications()
            ->where('data->community_id', $community->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    }

    public function readAll(Request $request, string $community_slug): RedirectResponse
    {
        $community = $this->context->require();

        $request->user()->unreadNotifications()
            ->where('data->community_id', $community->id)
            ->get()
            ->markAsRead();

        return back();
    }
}

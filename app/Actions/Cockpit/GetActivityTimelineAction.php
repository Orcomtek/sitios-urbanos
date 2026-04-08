<?php

namespace App\Actions\Cockpit;

use App\Models\User;
use App\Models\Community;
use App\Enums\CommunityRole;
use App\Models\SecurityLog;
use Illuminate\Support\Str;

class GetActivityTimelineAction
{
    public function execute(User $user, Community $community, int $limit = 20): array
    {
        $role = $user->roleInCommunity($community);
        $items = collect();

        if ($role === CommunityRole::Admin || $role === CommunityRole::Guard) {
            // Admin/Guard -> Tenant operational SecurityLog events
            $logs = SecurityLog::where('community_id', $community->id)
                ->latest('created_at')
                ->take($limit)
                ->get();
                
            $items = $items->merge($logs->map(function (SecurityLog $log) {
                return $this->mapSecurityLog($log);
            }));
            
        } elseif ($role === CommunityRole::Resident) {
            // Resident -> rely strictly on tenant-filtered notifications
            $notifications = $user->notifications()
                ->where('data->community_id', $community->id)
                ->latest()
                ->take($limit)
                ->get();
                
            $items = $items->merge($notifications->map(function ($notification) {
                return $this->mapNotification($notification);
            }));
        }

        // Sort descending by created_at and slice just in case we hit limits differently
        return $items->sortByDesc('created_at')->take($limit)->values()->all();
    }

    private function mapSecurityLog(SecurityLog $log): array
    {
        $type = Str::snake($log->action);
        
        $title = Str::title(str_replace('_', ' ', $type));
        if (isset($log->details['title'])) {
            $title = $log->details['title'];
        }

        $message = $log->details['message'] ?? 'Operación registrada en el sistema.';

        return [
            'id' => 'log_' . $log->id,
            'source' => 'security_log',
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'created_at' => $log->created_at->toIso8601String(),
            'entity_id' => $log->subject_id,
            'entity_type' => $log->subject_type,
            'metadata' => [
                'actor_id' => $log->actor_id,
            ],
        ];
    }

    private function mapNotification($notification): array
    {
        $classParts = explode('\\', $notification->type);
        $baseName = str_replace('Notification', '', end($classParts));
        $type = Str::snake($baseName);

        $data = $notification->data;
        
        return [
            'id' => 'notif_' . $notification->id,
            'source' => 'notification',
            'type' => $type,
            'title' => $data['title'] ?? 'Nueva Notificación',
            'message' => $data['message'] ?? 'Tienes una actualización del sistema.',
            'created_at' => $notification->created_at->toIso8601String(),
            'entity_id' => $data['entity_id'] ?? null,
            'entity_type' => $data['entity_type'] ?? null,
            'metadata' => [
                'action_url' => $data['action_url'] ?? null,
            ],
        ];
    }
}

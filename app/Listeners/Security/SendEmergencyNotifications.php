<?php

namespace App\Listeners\Security;

use App\Events\Security\EmergencyEventCreated;
use App\Models\User;
use App\Notifications\Security\EmergencyEventTriggeredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendEmergencyNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EmergencyEventCreated $event): void
    {
        $community = $event->emergency->community;

        // Fetch users who are admin or guard in this community
        // Assuming pivoting on `community_user` handles roles, but we need users
        // Let's get users using the relationship or querying whereHas
        $usersToNotify = User::whereHas('communities', function ($query) use ($community) {
            $query->where('community_id', $community->id)
                  ->whereIn('role', ['admin', 'guard']);
        })->get();

        Notification::send($usersToNotify, new EmergencyEventTriggeredNotification($event->emergency));
    }
}

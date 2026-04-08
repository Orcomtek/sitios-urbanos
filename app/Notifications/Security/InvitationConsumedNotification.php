<?php

namespace App\Notifications\Security;

use App\Models\AccessInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvitationConsumedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AccessInvitation $accessInvitation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'community_id' => $this->accessInvitation->community_id,
            'title' => 'Invitación Consumida',
            'message' => 'Tu invitación para '.$this->accessInvitation->guest_name.' ha sido registrada en portería.',
            'type' => 'invitation_consumed',
            'entity_id' => $this->accessInvitation->id,
            'entity_type' => 'access_invitation',
        ];
    }
}

<?php

namespace App\Notifications\Security;

use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VisitorRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Visitor $visitor) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'community_id' => $this->visitor->community_id,
            'title' => 'Visitante Registrado',
            'message' => 'Un visitante ('.$this->visitor->name.') ha registrado su entrada hacia tu unidad.',
            'type' => 'visitor_registered',
            'entity_id' => $this->visitor->id,
            'entity_type' => 'visitor',
        ];
    }
}

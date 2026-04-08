<?php

namespace App\Notifications\Security;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PackageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Package $package) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'community_id' => $this->package->community_id,
            'title' => 'Paquete Recibido',
            'message' => 'Tienes un nuevo paquete ('.$this->package->carrier.') en portería.',
            'type' => 'package_received',
            'entity_id' => $this->package->id,
            'entity_type' => 'package',
        ];
    }
}

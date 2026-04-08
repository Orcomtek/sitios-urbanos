<?php

namespace App\Events\Security;

use App\Models\EmergencyEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyEventCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public EmergencyEvent $emergency) {}
}

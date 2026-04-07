<?php

namespace App\Notifications\Security;

use App\Models\EmergencyEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyEventTriggeredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmergencyEvent $emergency)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unitNumber = $this->emergency->unit->number ?? 'N/A';
        return (new MailMessage)
            ->error()
            ->subject('¡ALERTA!: Botón de Pánico Activado - ' . $this->emergency->type)
            ->greeting('¡Atención!')
            ->line('Se ha activado una alerta (' . strtoupper($this->emergency->type) . ') en la unidad ' . $unitNumber . ' de la comunidad ' . $this->emergency->community->name . '.')
            ->line('Descripción: ' . ($this->emergency->description ?: 'Sin descripción adicional.'))
            ->action('Ver Alerta', route('tenant.dashboard', ['community_slug' => $this->emergency->community->slug])) // Adjust URL if needed
            ->line('Por favor atienda esta situación de forma inmediata.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'emergency_id' => $this->emergency->id,
        ];
    }
}

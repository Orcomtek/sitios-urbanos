<?php

namespace App\Notifications\Governance;

use App\Models\Pqrs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PqrsUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pqrs $pqrs)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Actualización de tu PQRS - '.config('app.name'))
            ->greeting('¡Hola!')
            ->line('Tu PQRS con asunto "'.$this->pqrs->subject.'" ha sido actualizado.')
            ->line('Nuevo estado: '.$this->pqrs->status->label())
            ->line('Respuesta de la administración:')
            ->line($this->pqrs->admin_response ?? 'El estado de tu PQRS ha sido modificado.')
            ->action('Ver Detalles', route('tenant.dashboard', ['community_slug' => $this->pqrs->community->slug]))
            ->line('Gracias por usar nuestra plataforma.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'community_id' => $this->pqrs->community_id,
            'title' => 'PQRS Actualizado',
            'message' => 'Tu PQRS "'.$this->pqrs->subject.'" ha sido actualizado a: '.$this->pqrs->status->label(),
            'type' => 'pqrs_updated',
            'entity_id' => $this->pqrs->id,
            'entity_type' => 'pqrs',
        ];
    }
}

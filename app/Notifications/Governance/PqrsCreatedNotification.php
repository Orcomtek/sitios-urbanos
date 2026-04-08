<?php

namespace App\Notifications\Governance;

use App\Models\Pqrs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PqrsCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Pqrs $pqrs)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo PQRS registrado - '.config('app.name'))
            ->greeting('¡Hola, Administrador!')
            ->line('Se ha registrado un nuevo PQRS ('.$this->pqrs->type->label().') en la comunidad '.$this->pqrs->community->name.'.')
            ->line('Asunto: '.$this->pqrs->subject)
            ->action('Ver Detalles', route('tenant.dashboard', ['community_slug' => $this->pqrs->community->slug]))
            ->line('Por favor ingresa a la plataforma para gestionar la solicitud.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'community_id' => $this->pqrs->community_id,
            'title' => 'Nuevo PQRS',
            'message' => 'Se ha registrado un nuevo PQRS: '.$this->pqrs->subject,
            'type' => 'pqrs_created',
            'entity_id' => $this->pqrs->id,
            'entity_type' => 'pqrs',
        ];
    }
}

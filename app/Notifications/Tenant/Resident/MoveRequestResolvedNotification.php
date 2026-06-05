<?php

namespace App\Notifications\Tenant\Resident;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MoveRequest;

class MoveRequestResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $moveRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(MoveRequest $moveRequest)
    {
        $this->moveRequest = $moveRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->getStatusText($this->moveRequest->status);

        $translatedType = match ($this->moveRequest->type) {
            'move_in' => 'Trasteo de Entrada',
            'move_out' => 'Trasteo de Salida',
            'internal_transfer' => 'Traslado Interno',
            default => 'Mudanza',
        };

        $translatedScale = match ($this->moveRequest->scale) {
            'light' => 'Ligera (Cajas/Artículos Menores)',
            'medium' => 'Mediana (Mobiliario Parcial)',
            'heavy' => 'Grande (Trasteo Completo)',
            default => 'No Especificada',
        };
        
        $mailMessage = (new MailMessage)
            ->subject('Actualización de su Solicitud de Mudanza - ' . $this->moveRequest->unit->identifier)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Le informamos que su solicitud de mudanza/traslado para la unidad ' . $this->moveRequest->unit->identifier . ' ha sido revisada.')
            ->line('**Tipo de Solicitud:** ' . $translatedType)
            ->line('**Magnitud:** ' . $translatedScale)
            ->line('Estado actual de la solicitud: **' . $statusText . '**');

        if ($this->moveRequest->status === 'modified') {
            $date = \Carbon\Carbon::parse($this->moveRequest->requested_date)->format('d/m/Y');
            $startTime = \Carbon\Carbon::parse($this->moveRequest->start_time)->format('h:i A');
            $endTime = \Carbon\Carbon::parse($this->moveRequest->end_time)->format('h:i A');

            $mailMessage->line('La administración ha propuesto un nuevo horario para su mudanza:');
            $mailMessage->line('**Fecha:** ' . $date);
            $mailMessage->line('**Horario:** ' . $startTime . ' - ' . $endTime);
        }

        if ($this->moveRequest->admin_notes) {
            $mailMessage->line('**Observaciones de la Administración:**');
            $mailMessage->line($this->moveRequest->admin_notes);
        }

        $url = route('tenant.resident.logistics.moves.index', ['community_slug' => $this->moveRequest->community->slug]);

        return $mailMessage
            ->action('Ver Mis Solicitudes', $url)
            ->line('Gracias por su colaboración.');
    }

    private function getStatusText(string $status): string
    {
        return match ($status) {
            'approved' => 'Aprobada',
            'denied' => 'Rechazada',
            'modified' => 'Aprobada con Modificaciones',
            default => 'Pendiente',
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

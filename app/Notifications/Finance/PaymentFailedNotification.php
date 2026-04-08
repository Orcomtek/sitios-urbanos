<?php

namespace App\Notifications\Finance;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Payment $payment)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amountCOP = number_format($this->payment->amount, 0, ',', '.');
        $unitName = $this->payment->unit?->number ?? 'tu unidad';

        return (new MailMessage)
            ->subject('Pago Fallido - '.config('app.name'))
            ->greeting('¡Hola!')
            ->line("Te informamos que tu intento de pago por un monto de $ {$amountCOP} COP asociado a la unidad {$unitName} ha fallado o fue rechazado por la entidad.")
            ->line('Por favor, verifica tus datos e intenta nuevamente más tarde. Si el problema persiste, contacta a la administración.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amountCOP = number_format($this->payment->amount, 0, ',', '.');

        return [
            'community_id' => $this->payment->community_id,
            'title' => 'Pago Fallido',
            'message' => "Tu intento de pago por $ {$amountCOP} COP ha fallado.",
            'type' => 'payment_failed',
            'entity_id' => $this->payment->id,
            'entity_type' => 'payment',
        ];
    }
}

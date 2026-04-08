<?php

namespace App\Notifications\Finance;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amountCOP = number_format($this->payment->amount, 0, ',', '.');
        $unitName = $this->payment->unit?->number ?? 'tu unidad';
        $invoiceBase = $this->payment->invoice ? ' de tu cobro' : '';

        return (new MailMessage)
            ->subject('Pago Confirmado - '.config('app.name'))
            ->greeting('¡Hola!')
            ->line("Te informamos que tu pago por un monto de $ {$amountCOP} COP asociado a la unidad {$unitName} ha sido confirmado con éxito{$invoiceBase}.")
            ->line('Gracias por utilizar nuestra plataforma para tus pagos.');
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
            'title' => 'Pago Confirmado',
            'message' => "Tu pago por $ {$amountCOP} COP ha sido confirmado.",
            'type' => 'payment_confirmed',
            'entity_id' => $this->payment->id,
            'entity_type' => 'payment',
        ];
    }
}

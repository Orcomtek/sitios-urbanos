<?php

namespace App\Listeners\Finance;

use App\Events\Finance\PaymentConfirmed;
use App\Events\Finance\PaymentFailed;
use App\Notifications\Finance\PaymentConfirmedNotification;
use App\Notifications\Finance\PaymentFailedNotification;

class SendPaymentNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed|PaymentFailed $event): void
    {
        $user = $event->payment->invoice?->resident?->user;

        if (! $user) {
            return;
        }

        if ($event instanceof PaymentConfirmed) {
            $user->notify(new PaymentConfirmedNotification($event->payment));
        } else {
            $user->notify(new PaymentFailedNotification($event->payment));
        }
    }
}

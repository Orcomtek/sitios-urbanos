<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case INTERNAL_EPAYCO = 'internal_epayco';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case MANUAL_OFFICE = 'manual_office';
}

<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case INTERNAL_EPAYCO = 'internal_epayco';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case CHECK = 'check';
    case POS_TERMINAL = 'pos_terminal';
    case MANUAL_OFFICE = 'manual_office';
}

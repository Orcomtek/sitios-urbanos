<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case CHARGE = 'charge';
    case PAYMENT = 'payment';
    case PLATFORM_COMMISSION = 'platform_commission';
    case EXTERNAL_FEE = 'external_fee';
    case ADJUSTMENT = 'adjustment';
    case REVERSAL = 'reversal';
}

<?php

namespace App\Enums;

enum InvoiceType: string
{
    case ADMIN_FEE = 'admin_fee';
    case FINE = 'fine';
    case EXTRAORDINARY = 'extraordinary';
}

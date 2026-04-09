<?php

namespace App\Enums;

enum ProviderCategory: string
{
    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case CLEANING = 'cleaning';
    case MAINTENANCE = 'maintenance';
    case OTHERS = 'others';
}

<?php

namespace App\Enums;

enum ServiceUrgency: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case EMERGENCY = 'emergency';
}

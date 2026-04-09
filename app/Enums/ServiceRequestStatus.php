<?php

namespace App\Enums;

enum ServiceRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

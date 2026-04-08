<?php

declare(strict_types=1);

namespace App\Enums;

enum ListingStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Reported = 'reported';
    case Removed = 'removed';
}

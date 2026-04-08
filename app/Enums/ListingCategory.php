<?php

declare(strict_types=1);

namespace App\Enums;

enum ListingCategory: string
{
    case Items = 'items';
    case Services = 'services';
    case RealEstate = 'real_estate';
}

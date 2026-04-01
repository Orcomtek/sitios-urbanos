<?php

namespace App\Enums;

enum CommunityRole: string
{
    case Admin = 'admin';
    case Resident = 'resident';
    case Guard = 'guard';
}

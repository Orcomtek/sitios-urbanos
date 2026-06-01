<?php

namespace App\Enums;

enum CommunityRole: string
{
    case TenantAdmin = 'tenant_admin';
    case SubAdmin = 'sub_admin';
    case Accountant = 'accountant';
    case Auditor = 'auditor';
    case Resident = 'resident';
    case Guard = 'guard';
}

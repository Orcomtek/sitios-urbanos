<?php

namespace App\Enums;

enum ResidentType: string
{
    case OWNER = 'owner';
    case TENANT = 'tenant';
    case DEPENDENT = 'dependent';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Propietario',
            self::TENANT => 'Inquilino',
            self::DEPENDENT => 'Residente (Dependiente)',
        };
    }
}

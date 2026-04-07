<?php

namespace App\Enums;

enum PqrsType: string
{
    case PETITION = 'petition';
    case COMPLAINT = 'complaint';
    case CLAIM = 'claim';
    case SUGGESTION = 'suggestion';

    public function label(): string
    {
        return match ($this) {
            self::PETITION => 'Petición',
            self::COMPLAINT => 'Queja',
            self::CLAIM => 'Reclamo',
            self::SUGGESTION => 'Sugerencia',
        };
    }
}

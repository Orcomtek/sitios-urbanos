<?php

namespace App\Enums;

enum PqrsStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Abierto',
            self::IN_PROGRESS => 'En Progreso',
            self::CLOSED => 'Cerrado',
            self::REJECTED => 'Rechazado',
        };
    }
}

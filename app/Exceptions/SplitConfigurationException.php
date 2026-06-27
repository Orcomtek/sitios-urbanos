<?php

namespace App\Exceptions;

use RuntimeException;

class SplitConfigurationException extends RuntimeException
{
    public static function missingAlliedAccount(int $communityId): self
    {
        return new self("Community [{$communityId}] does not have an ePayco allied account configured. Split payments cannot proceed.");
    }
}

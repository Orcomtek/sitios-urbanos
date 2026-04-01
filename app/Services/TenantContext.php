<?php

namespace App\Services;

use App\Exceptions\TenantContextMissingException;
use App\Models\Community;

class TenantContext
{
    private ?Community $community = null;

    public function set(Community $community): void
    {
        $this->community = $community;
    }

    public function get(): ?Community
    {
        return $this->community;
    }

    public function require(): Community
    {
        if ($this->community === null) {
            throw new TenantContextMissingException('Tenant context is required but not set.');
        }

        return $this->community;
    }
}

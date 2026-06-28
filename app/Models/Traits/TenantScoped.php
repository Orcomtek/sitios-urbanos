<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantContext;

trait TenantScoped
{
    /**
     * Boot the tenant scoped trait for a model.
     */
    public static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            // Always assign community_id from the active tenant context
            // to prevent payload manipulation attacks.
            $context = app(TenantContext::class);
            $community = $context->get();

            if ($community !== null) {
                $model->community_id = $community->id;
            }
        });
    }
}

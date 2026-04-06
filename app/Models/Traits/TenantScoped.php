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
            // Automatically assign community_id if an active tenant context exists
            // and it hasn't been manually set (e.g. by a seeder or explicit code).
            if (! $model->community_id) {
                $context = app(TenantContext::class);
                $community = $context->get();

                if ($community !== null) {
                    $model->community_id = $community->id;
                }
            }
        });
    }
}

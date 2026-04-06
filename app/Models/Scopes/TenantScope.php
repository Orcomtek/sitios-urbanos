<?php

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);
        $community = $context->get();

        // Only enforce the scope if a TenantContext is explicitly active.
        // This strictly hardens the tenant runtime without breaking
        // seeders, background jobs, or control-plane logic.
        if ($community !== null) {
            $builder->where($model->getTable().'.community_id', $community->id);
        }
    }
}

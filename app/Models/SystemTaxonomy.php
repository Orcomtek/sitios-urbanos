<?php

namespace App\Models;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemTaxonomy extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'community_id',
        'type',
        'label',
        'value',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active records that are either global (community_id = null)
     * or belong to the current active tenant.
     */
    public function scopeForCurrentTenantOrGlobal(Builder $query): void
    {
        $tenantId = app(TenantContext::class)->get()?->id;

        $query->where('is_active', true)
              ->where(function (Builder $query) use ($tenantId) {
                  $query->whereNull('community_id');
                  if ($tenantId) {
                      $query->orWhere('community_id', $tenantId);
                  }
              });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingConcept extends Model
{
    protected $fillable = [
        'community_id',
        'code',
        'name',
        'type',
        'is_commissionable',
        'is_active',
    ];

    protected $casts = [
        'is_commissionable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}

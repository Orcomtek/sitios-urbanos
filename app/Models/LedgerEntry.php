<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory, HasUuids, TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \RuntimeException('Ledger entries represent immutable financial history and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \RuntimeException('Ledger entries represent immutable financial history and cannot be deleted.');
        });
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

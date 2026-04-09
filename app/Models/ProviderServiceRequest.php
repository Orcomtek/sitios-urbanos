<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use App\Enums\ServiceUrgency;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderServiceRequest extends Model
{
    use HasFactory;
    use HasUuids;
    use TenantScoped;

    protected $fillable = [
        'description',
        'status',
        'urgency',
        'scheduled_date',
        'resident_id',
        'provider_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ServiceRequestStatus::class,
            'urgency' => ServiceUrgency::class,
            'scheduled_date' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Community, clone $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<Resident, clone $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * @return BelongsTo<Provider, clone $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}

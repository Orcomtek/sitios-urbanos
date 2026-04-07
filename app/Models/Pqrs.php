<?php

namespace App\Models;

use App\Enums\PqrsStatus;
use App\Enums\PqrsType;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pqrs extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'community_id',
        'resident_id',
        'type',
        'status',
        'subject',
        'description',
        'is_anonymous',
        'admin_response',
        'responded_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PqrsType::class,
            'status' => PqrsStatus::class,
            'is_anonymous' => 'boolean',
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Override toArray to strictly protect anonymous identities in all serializations.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // If anonymous, ensure resident_id and loaded resident relationships are removed from the output.
        // This acts as a robust output-level protection mechanism.
        if ($this->is_anonymous) {
            unset($array['resident_id']);
            unset($array['resident']);
        }

        return $array;
    }
}

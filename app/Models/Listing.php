<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ListingCategory;
use App\Enums\ListingStatus;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Listing extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'community_id',
        'resident_id',
        'title',
        'description',
        'price',
        'category',
        'status',
        'show_contact_info',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'category' => ListingCategory::class,
            'status' => ListingStatus::class,
            'show_contact_info' => 'boolean',
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
}

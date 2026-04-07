<?php

namespace App\Models\Governance;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'community_id',
        'title',
        'content',
        'type',
        'target_audience',
        'attachment_url',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}

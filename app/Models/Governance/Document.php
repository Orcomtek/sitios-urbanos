<?php

namespace App\Models\Governance;

use App\Models\Traits\TenantScoped;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'document_type',
        'file_url',
        'file_size',
        'mime_type',
        'visibility',
        'created_by',
    ];

    protected $casts = [
        'visibility' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

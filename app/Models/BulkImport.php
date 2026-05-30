<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkImport extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'user_id',
        'type',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'errors',
        'file_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }

    /**
     * Get the community that owns the bulk import.
     */
    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the user that initiated the bulk import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

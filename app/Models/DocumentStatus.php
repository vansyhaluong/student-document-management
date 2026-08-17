<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentStatus extends Model
{
    protected $fillable = [
        'code',
        'label',
        'badge_class',
        'color_hex',
        'sort_order',
        'is_system',
        'is_active',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'status', 'code');
    }

    public function historyEntries(): HasMany
    {
        return $this->hasMany(DocumentStatusHistory::class, 'status', 'code');
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

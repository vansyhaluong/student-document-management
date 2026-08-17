<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'document_type_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

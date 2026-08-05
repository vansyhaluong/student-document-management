<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getValueAttribute(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->attributes['label'];
    }

    public function badgeClass(): string
    {
        return $this->attributes['badge_class'];
    }

    public function isCode(string $code): bool
    {
        return $this->code === $code;
    }

    public static function cases(): array
    {
        return static::orderBy('sort_order')->get()->all();
    }

    public static function activeCases(): array
    {
        return static::where('is_active', true)->orderBy('sort_order')->get()->all();
    }

    public static function from(string $code): self
    {
        return static::where('code', $code)->firstOrFail();
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'status', 'code');
    }
}

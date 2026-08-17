<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $primaryKey = 'student_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'student_code',
        'last_name',
        'first_name',
        'date_of_birth',
        'phone_number',
        'email',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'student_code', 'student_code');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name}");
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }
}

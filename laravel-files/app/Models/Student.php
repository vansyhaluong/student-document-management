<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'student_code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_code',
        'last_name',
        'first_name',
        'date_of_birth',
        'phone_number',
        'email',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'student_code', 'student_code');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name}");
    }
}

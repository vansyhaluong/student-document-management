<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    const CREATED_AT = null; // bảng không có created_at, chỉ có submitted_at + updated_at
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'document_code',
        'student_code',
        'document_type_id',
        'status',
        'assigned_secretary_user_id',
        'submitted_at',
        'completed_at',
        'invalid_reason',
        'note',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function assignedSecretary()
    {
        return $this->belongsTo(User::class, 'assigned_secretary_user_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(DocumentStatusHistory::class);
    }
}

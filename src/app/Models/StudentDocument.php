<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    const CREATED_AT = null;

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
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? DocumentStatus::from($value) : null,
            set: fn ($value) => $value instanceof DocumentStatus ? $value->code : $value,
        );
    }

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

    public function statusUpdatePermission($user): array
    {
        if ($user->role === 'staff') {
            if ($this->status->isCode('waiting_for_receipt')) {
                return [true, [DocumentStatus::from('received')]];
            }

            return [false, []];
        }

        return [true, DocumentStatus::cases()];
    }
}

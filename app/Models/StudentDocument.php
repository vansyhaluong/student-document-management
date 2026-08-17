<?php

namespace App\Models;

use App\Enums\StudentDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentDocument extends Model
{
    public const CREATED_AT = null;

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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class, 'status', 'code');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_secretary_user_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(DocumentStatusHistory::class, 'student_document_id')
            ->orderBy('changed_at');
    }

    protected function casts(): array
    {
        return [
            'document_type_id' => 'integer',
            'assigned_secretary_user_id' => 'integer',
            'status' => StudentDocumentStatus::class,
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

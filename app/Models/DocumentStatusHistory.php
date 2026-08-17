<?php

namespace App\Models;

use App\Enums\StudentDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStatusHistory extends Model
{
    protected $table = 'document_status_history';

    public $timestamps = false;

    protected $fillable = [
        'student_document_id',
        'status',
        'invalid_reason',
        'note',
        'changed_by_user_id',
        'changed_at',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'student_document_id' => 'integer',
            'changed_by_user_id' => 'integer',
            'status' => StudentDocumentStatus::class,
            'changed_at' => 'datetime',
        ];
    }
}

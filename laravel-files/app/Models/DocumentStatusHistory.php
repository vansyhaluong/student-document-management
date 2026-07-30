<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;

class DocumentStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_document_id',
        'status',
        'invalid_reason',
        'note',
        'changed_by_user_id',
        'changed_at',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'changed_at' => 'datetime',
    ];

    public function studentDocument()
    {
        return $this->belongsTo(StudentDocument::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? DocumentStatus::from($value) : null,
            set: fn ($value) => $value instanceof DocumentStatus ? $value->code : $value,
        );
    }

    public function studentDocument()
    {
        return $this->belongsTo(StudentDocument::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
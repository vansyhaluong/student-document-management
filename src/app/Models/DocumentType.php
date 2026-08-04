<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    // Bảng document_types không có created_at/updated_at (xem SQL gốc)
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Chỉ lấy các loại đơn đang bật, sắp theo tên.
     * Dùng cho bước "Chọn loại đơn" (SRS 3.2.6: chỉ hiển thị loại đơn có trong database).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name');
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'document_type_id');
    }
}

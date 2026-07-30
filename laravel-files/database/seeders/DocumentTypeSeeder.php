<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

/**
 * Seed 6 "loại hồ sơ" theo phạm vi dự án (SRS mục 1.2).
 * LƯU Ý: danh sách này vẫn là dữ liệu tạm thời — xem README, cần thay bằng danh
 * sách chính thức khi giáo viên duyệt xong (biên bản họp mục IV).
 */
class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'nghi_hoc_tam_thoi',
                'name' => 'Đơn xin nghỉ học tạm thời',
                'description' => 'Đơn đề nghị tạm dừng học tập trong một khoảng thời gian xác định.',
            ],
            [
                'code' => 'bao_luu_ket_qua',
                'name' => 'Đơn xin bảo lưu kết quả học tập',
                'description' => 'Đơn đề nghị bảo lưu kết quả học tập đã tích lũy.',
            ],
            [
                'code' => 'xac_nhan_sinh_vien',
                'name' => 'Giấy xác nhận là sinh viên',
                'description' => 'Xác nhận tình trạng đang theo học tại trường, phục vụ vay vốn/khai báo.',
            ],
            [
                'code' => 'cap_bang_diem',
                'name' => 'Đơn xin cấp bảng điểm',
                'description' => 'Đề nghị cấp bản sao/bản chính bảng điểm quá trình học tập.',
            ],
            [
                'code' => 'phuc_khao_diem',
                'name' => 'Đơn xin phúc khảo điểm thi',
                'description' => 'Đề nghị chấm phúc khảo lại bài thi kết thúc học phần.',
            ],
            [
                'code' => 'hoc_bong_tro_cap',
                'name' => 'Đơn xin học bổng / trợ cấp',
                'description' => 'Đề nghị xét học bổng khuyến khích học tập hoặc trợ cấp xã hội.',
            ],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}

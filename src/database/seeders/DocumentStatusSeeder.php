<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'waiting_for_receipt', 'label' => 'Chờ tiếp nhận',   'badge_class' => 'badge-cho',            'color_hex' => '#1e4fd6', 'sort_order' => 1],
            ['code' => 'received',            'label' => 'Đã tiếp nhận',   'badge_class' => 'badge-tiep-nhan',      'color_hex' => '#16a34a', 'sort_order' => 2],
            ['code' => 'processing',          'label' => 'Đang xử lý',     'badge_class' => 'badge-dang-xu-ly',     'color_hex' => '#f59e0b', 'sort_order' => 3],
            ['code' => 'needs_supplement',    'label' => 'Cần bổ sung',    'badge_class' => 'badge-bo-sung',        'color_hex' => '#eab308', 'sort_order' => 4],
            ['code' => 'completed',           'label' => 'Hoàn tất',       'badge_class' => 'badge-hoan-tat',       'color_hex' => '#7c3aed', 'sort_order' => 5],
            ['code' => 'invalid',             'label' => 'Không hợp lệ',   'badge_class' => 'badge-khong-hop-le',   'color_hex' => '#dc2626', 'sort_order' => 6],
            ['code' => 'cancelled',           'label' => 'Đã hủy',         'badge_class' => 'badge-huy',            'color_hex' => '#6b7280', 'sort_order' => 7],
        ];

        foreach ($statuses as $s) {
            DB::table('document_statuses')->updateOrInsert(
                ['code' => $s['code']],
                [
                    'label' => $s['label'],
                    'badge_class' => $s['badge_class'],
                    'color_hex' => $s['color_hex'],
                    'sort_order' => $s['sort_order'],
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

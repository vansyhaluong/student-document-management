<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dữ liệu khởi động (danh mục cơ bản) để ứng dụng chạy được ngay sau khi migrate.
     * Các seeder nghiệp vụ khác (users mặc định, students mẫu...) sẽ bổ sung ở HĐ sau,
     * theo đúng schema đã chốt trong HD2_KienTrucUngDung mục 3.1.
     */
    public function run(): void
    {
        $this->call([
            DocumentTypeSeeder::class,
        ]);
    }
}

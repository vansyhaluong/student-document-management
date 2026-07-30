# Áp dụng schema thật (student_document_management_schema.sql)

## 1. Vào thư mục `src\database\migrations`, XÓA các file migration cũ sau:
- `0001_01_01_000000_create_users_table.php` (mặc định Laravel — trùng bảng `users` với schema thật)
- `2026_07_30_000001_create_document_types_table.php` (bản cũ mình soạn sai cột — sẽ bị thay bằng bản mới)

Giữ nguyên `0001_01_01_000001_create_cache_table.php` và `0001_01_01_000002_create_jobs_table.php`
(không liên quan bảng users, vẫn cần cho cache/queue).

## 2. Copy toàn bộ file trong `database/migrations/` của gói này vào `src\database\migrations\`
Gồm 6 file (chạy theo đúng thứ tự tên file, vì Laravel migrate theo alphabet/timestamp):
```
2026_07_30_000000_create_password_reset_and_sessions_tables.php
2026_07_30_000001_create_document_types_table.php
2026_07_30_000002_create_students_table.php
2026_07_30_000003_create_users_table.php
2026_07_30_000004_create_student_documents_table.php
2026_07_30_000005_create_document_status_history_table.php
```

## 3. Copy các Model vào `src\app\Models\` (ghi đè `DocumentType.php` cũ)
```
DocumentType.php
Student.php
User.php
StudentDocument.php
DocumentStatusHistory.php
```

## 4. Copy Enum vào `src\app\Enums\` (tạo thư mục Enums nếu chưa có)
```
DocumentStatus.php
```

## 5. Copy seeder, ghi đè bản cũ
```
database/seeders/DocumentTypeSeeder.php  → src\database\seeders\DocumentTypeSeeder.php
```
(`DatabaseSeeder.php` giữ nguyên bản trước, không cần đổi — vẫn chỉ gọi `DocumentTypeSeeder`)

## 6. Reset lại toàn bộ database và chạy migrate + seed

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Vì thay đổi cấu trúc bảng `users`/`document_types` hoàn toàn, **bắt buộc phải `migrate:fresh`**
(không dùng `migrate` thường), vì đây là lần cuối DB còn "sạch" — sau bước này nếu đổi schema nữa
sẽ mất dữ liệu nếu đã có dữ liệu SV/đơn thật.

## 7. Kiểm tra

```bash
docker compose exec app php artisan tinker
```
```php
App\Models\DocumentType::count();   // 6
Schema::hasTable('student_documents');  // true
Schema::hasTable('document_status_history');  // true
```

Cũng có thể mở phpMyAdmin (`http://localhost:8080`) để xem trực quan 5 bảng: `document_types`,
`students`, `users`, `student_documents`, `document_status_history` — kiểm tra khóa ngoại và
CHECK constraint đã được tạo đúng trong tab "Structure" / "Relation view".

## Điểm cần lưu ý khi viết code nghiệp vụ sau này

- **Role** dùng `staff` / `secretary` / `admin` (không phải `nhan_vien`/`thu_ky` như tài liệu cũ).
- **Status enum** dùng `App\Enums\DocumentStatus` với 7 giá trị thật (không phải 4 giá trị
  `submitted/received/transferred/cancelled` như bản nháp trước).
- **`Student`** không có cột `id`, khóa chính là `student_code` (string) — Model đã cấu hình
  `$incrementing = false`, `$keyType = 'string'`.
- **`document_code`** trong `student_documents` bị khóa không cho sửa sau khi tạo (trigger MySQL) —
  nếu code Laravel cố update cột này, MySQL sẽ tự chặn và ném lỗi `SQLSTATE[45000]`.

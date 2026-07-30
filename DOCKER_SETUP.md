# DocTrack — Khởi tạo bằng Docker Desktop (ADR-002)

Đây là phương án đã chốt chính thức trong ADR-002 (HD3_phan_tich_trong_so mục 3: Docker Compose
đạt điểm tổng cao nhất — 4.05, nhờ tái lập chính xác môi trường production và cô lập service).

## Cấu trúc thư mục sau khi giải nén

```
doctrack-docker/
├── docker/Dockerfile          # PHP 8.3 + extensions + Composer
├── docker-compose.yml         # app (Laravel) + db (MySQL 8) + phpmyadmin
├── env.docker.example         # mẫu .env đã set DB_HOST=db, timezone, locale vi
├── src/                       # (rỗng) — Laravel sẽ được tạo vào đây
└── laravel-files/             # các file đã soạn sẵn, copy vào src/ sau khi tạo project
```

## Bước 1 — Mở terminal tại thư mục `doctrack-docker/`

Đảm bảo Docker Desktop đang chạy.

## Bước 2 — Build image

```bash
docker compose build
```

## Bước 3 — Tạo project Laravel vào thư mục `src/` (chạy 1 lần)

```bash
docker compose run --rm app composer create-project laravel/laravel . "11.*"
docker compose run --rm app composer require spatie/laravel-permission spatie/laravel-activitylog
```

(Lệnh `composer create-project ... .` tạo Laravel ngay trong container, nhưng vì `src/` được mount
volume nên toàn bộ code sẽ xuất hiện luôn trên máy bạn tại `doctrack-docker/src/`.)

## Bước 4 — Copy các file đã soạn sẵn từ `laravel-files/` vào `src/`

| Từ `laravel-files/...` | Copy đè vào `src/...` |
|---|---|
| `config/app.php` | `src/config/app.php` |
| `database/migrations/2026_07_30_000001_..._document_types_table.php` | `src/database/migrations/` |
| `database/seeders/DocumentTypeSeeder.php` | `src/database/seeders/` |
| `database/seeders/DatabaseSeeder.php` | `src/database/seeders/` (đè bản mặc định) |
| `app/Models/DocumentType.php` | `src/app/Models/` |

Trên macOS/Linux có thể copy nhanh bằng:
```bash
cp -r laravel-files/config/app.php src/config/app.php
cp laravel-files/database/migrations/*.php src/database/migrations/
cp laravel-files/database/seeders/*.php src/database/seeders/
cp laravel-files/app/Models/DocumentType.php src/app/Models/
```

## Bước 5 — Cấu hình `.env`

Mở `env.docker.example`, copy toàn bộ nội dung vào `src/.env` (ghi đè `.env` mặc định Laravel vừa
tạo). Lưu ý điểm khác so với chạy local: `DB_HOST=db` (tên service trong docker-compose), **không**
phải `127.0.0.1`.

## Bước 6 — Khởi động toàn bộ container

```bash
docker compose up -d
```

## Bước 7 — Sinh APP_KEY, migrate + seed (chạy lệnh artisan bên trong container `app`)

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Bước 8 — Kiểm tra

- Ứng dụng: http://localhost:8000
- phpMyAdmin (xem DB trực quan): http://localhost:8080 (user `root` / pass `root`)
- Kiểm tra seed:
  ```bash
  docker compose exec app php artisan tinker
  >>> App\Models\DocumentType::count();   // phải ra 6
  ```

## Các lệnh dùng thường xuyên sau này

```bash
docker compose up -d              # khởi động lại
docker compose down                # tắt (giữ data DB nhờ volume db_data)
docker compose exec app php artisan migrate:fresh --seed   # reset DB + seed lại
docker compose logs -f app         # xem log container app
```

## Lưu ý

Danh sách 6 "loại đơn" trong `DocumentTypeSeeder` vẫn là dữ liệu **tạm thời** (xem ghi chú trong
`doctrack-init/README_SETUP.md`) — chờ giáo viên duyệt danh sách chính thức theo biên bản họp.

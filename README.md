# DocTrack — Hệ thống Tra cứu & Nộp đơn Sinh viên

Đồ án môn học — Khoa Công nghệ Thông tin, Trường Cao đẳng Công nghệ Thủ Đức.

Hệ thống hỗ trợ sinh viên nộp hồ sơ (chứng chỉ bổ sung) trực tuyến và tra cứu tình trạng xử lý, đồng thời cung cấp khu vực quản trị cho Admin / Thư ký / Nhân viên xử lý hồ sơ.

---

## 1. Yêu cầu môi trường

Chỉ cần cài đúng **1 phần mềm** duy nhất, toàn bộ PHP/MySQL/Nginx đã được đóng gói sẵn trong Docker:

- **Docker Desktop** (Windows/Mac/Linux) — tải tại: https://www.docker.com/products/docker-desktop/
- Sau khi cài xong, mở Docker Desktop lên và để nó chạy nền (không cần đăng nhập tài khoản Docker Hub).
- Git (để clone code về máy) — tải tại: https://git-scm.com/downloads

Không cần cài PHP, Composer, MySQL, phpMyAdmin riêng lẻ trên máy — tất cả chạy trong container Docker.

---

## 2. Clone project về máy

Mở terminal (CMD/PowerShell/Terminal), chạy:

```bash
git clone <URL-repo-github-cua-ban>
cd doctrack-docker
```

---

## 3. Tạo file cấu hình môi trường (`.env`)

Trong thư mục `src/`, tạo file `.env` bằng cách copy từ file mẫu có sẵn:

```bash
cd src
cp .env.example .env
```

> Trên Windows CMD dùng: `copy .env.example .env`

Mở file `.env` vừa tạo, kiểm tra/đảm bảo đúng các dòng cấu hình kết nối database khớp với `docker-compose.yml` (thường đã đúng sẵn, không cần sửa nếu dùng đúng cấu hình gốc của dự án):

```env
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_LOCALE=vi

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=doctrack
DB_USERNAME=root
DB_PASSWORD=root
```

Quay lại thư mục gốc:
```bash
cd ..
```

---

## 4. Khởi động Docker

Tại thư mục gốc `doctrack-docker/` (chứa file `docker-compose.yml`), chạy:

```bash
docker compose up -d
```

Lần đầu chạy sẽ mất vài phút để tải image PHP/MySQL. Kiểm tra 3 container đã chạy:
```bash
docker compose ps
```
Phải thấy 3 container `app`, `db`, `phpmyadmin` đều ở trạng thái `Up`.

---

## 5. Cài đặt Laravel lần đầu (chỉ cần làm 1 lần)

Chạy lần lượt các lệnh sau (mỗi lệnh đợi chạy xong mới chạy lệnh tiếp theo):

```bash
# Cài các thư viện PHP (composer)
docker compose exec app composer install

# Tạo APP_KEY (mã hoá session/cookie riêng cho máy bạn)
docker compose exec app php artisan key:generate

# Tạo toàn bộ bảng trong database theo migration
docker compose exec app php artisan migrate

# Tạo bảng activity_log (ghi nhật ký thao tác hệ thống)
docker compose exec app php artisan vendor:publish --tag="activitylog-migrations"
docker compose exec app php artisan migrate
```

---

## 6. Import dữ liệu mẫu (sinh viên, loại chứng chỉ, tài khoản)

Nếu repo có kèm file SQL dữ liệu mẫu (ví dụ `du_lieu_that.sql`), import qua phpMyAdmin:

1. Mở `http://localhost:8080` (đăng nhập: username `root`, password `root`)
2. Chọn database `doctrack`
3. Vào tab **Import** → chọn file `.sql` → bấm **Go**

Nếu chưa có sẵn dữ liệu, tạo tài khoản quản trị đầu tiên bằng tinker:

```bash
docker compose exec app php artisan tinker
```

Trong tinker, chạy (thay thông tin theo ý bạn):
```php
\App\Models\User::create([
    'username' => 'admin',
    'password_hash' => \Illuminate\Support\Facades\Hash::make('admin123'),
    'full_name' => 'Quản trị viên',
    'email' => 'admin@doctrack.local',
    'role' => 'admin',
    'is_active' => 1,
]);
```

Gõ `exit` để thoát tinker.

---

## 7. Truy cập ứng dụng

| Địa chỉ | Mô tả |
|---|---|
| http://localhost:8000 | Trang chủ hệ thống (sinh viên nộp/tra cứu hồ sơ) |
| http://localhost:8000/login | Đăng nhập khu vực quản trị |
| http://localhost:8080 | phpMyAdmin (xem/sửa database trực tiếp) — user `root`, pass `root` |

---

## 8. Các lệnh hay dùng khi phát triển tiếp

```bash
# Xem danh sách route đã đăng ký
docker compose exec app php artisan route:list

# Xóa cache config sau khi sửa .env hoặc config/*.php
docker compose exec app php artisan config:clear

# Mở tinker để thao tác thử với database qua Eloquent
docker compose exec app php artisan tinker

# Xem log lỗi Laravel trực tiếp
docker compose exec app tail -f storage/logs/laravel.log

# Dừng toàn bộ container (không xóa dữ liệu)
docker compose down

# Khởi động lại sau khi đã cài lần đầu
docker compose up -d
```

---

## 9. Cấu trúc thư mục chính

```
doctrack-docker/
├── docker-compose.yml       # Cấu hình 3 container: app, db, phpmyadmin
└── src/                     # Toàn bộ code Laravel
    ├── app/
    │   ├── Enums/            # DocumentStatus (7 trạng thái xử lý đơn)
    │   ├── Http/Controllers/
    │   │   ├── Admin/        # Controller khu vực quản trị
    │   │   ├── Public/       # Controller nộp đơn/tra cứu (sinh viên)
    │   │   └── Auth/         # Đăng nhập/đăng xuất
    │   └── Models/           # Student, StudentDocument, DocumentType, User...
    ├── resources/views/
    │   ├── admin/            # Giao diện quản trị
    │   ├── public/           # Giao diện sinh viên
    │   └── layouts/          # Layout dùng chung
    ├── routes/web.php        # Toàn bộ route của hệ thống
    └── public/assets/        # CSS/JS/hình ảnh
```

---

## 10. Lưu ý quan trọng

- File `.env` **không được commit lên GitHub** (đã có trong `.gitignore`) vì chứa thông tin nhạy cảm. Mỗi máy clone về phải tự tạo `.env` riêng theo Bước 3.
- Nếu đổi cổng `8000`/`8080` do bị trùng với ứng dụng khác trên máy, sửa trong `docker-compose.yml` phần `ports:`.
- Múi giờ hệ thống đã cấu hình sẵn `Asia/Ho_Chi_Minh` — không cần chỉnh thêm.

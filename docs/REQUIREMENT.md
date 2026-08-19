# REQUIREMENT

## Hệ thống quản lý hồ sơ sinh viên nội bộ Khoa Công nghệ Thông tin

## 1. Mục tiêu và phạm vi

Xây dựng ứng dụng web nội bộ để lưu trữ, tra cứu, cập nhật và theo dõi quá trình
xử lý hồ sơ sinh viên. Hệ thống có quy mô nhỏ, ưu tiên dễ sử dụng, bảo mật và dễ
bảo trì.

Database `doctrack` trên MariaDB 10.11 là data model hiện hành. Phiên bản MVP
không có email, tài liệu đính kèm, tích hợp dịch vụ ngoài hoặc workflow engine.

## 2. Vai trò

Hệ thống chỉ có ba vai trò nghiệp vụ:

### 2.1. Admin (`ADMIN`)

- Quản lý tất cả hồ sơ.
- Quản lý loại hồ sơ và tài khoản người dùng.
- Gán vai trò, khóa/mở khóa và reset mật khẩu nội bộ.
- Xem dashboard, báo cáo và audit log.

### 2.2. Thư ký (`SECRETARY`)

- Xem, tạo, cập nhật và xử lý hồ sơ.
- Cập nhật trạng thái và xem lịch sử trạng thái.
- Tìm kiếm, lọc, xem dashboard và báo cáo.

### 2.3. Nhân viên (`EMPLOYEE`)

- Xem tất cả hồ sơ.
- Tìm kiếm và lọc hồ sơ.
- Cập nhật ghi chú khi policy cho phép.
- Chuyển trạng thái theo workflow đã duyệt.

Trong database, `EMPLOYEE` được lưu bằng giá trị legacy `staff`. Không có role
thứ tư. Backend phải enforce authorization; việc ẩn/hiện UI không phải cơ chế
bảo mật.

## 3. Xác thực

- Đăng nhập bằng `username` và password.
- Đăng xuất và xác định người dùng hiện tại.
- Chỉ tài khoản `is_active = 1` được truy cập chức năng nội bộ.
- Dùng session authentication.
- Password hiện hữu dùng bcrypt và phải tiếp tục được xác minh an toàn.
- Không triển khai đăng ký công khai, email verification, quên mật khẩu qua email
  hoặc bất kỳ email notification nào.
- Đăng nhập và các thay đổi bảo mật quan trọng phải được audit.

## 4. Dashboard

Dashboard sau đăng nhập hiển thị theo phạm vi quyền:

- Tổng số hồ sơ.
- Hồ sơ chờ tiếp nhận, đã tiếp nhận và đang xử lý.
- Hồ sơ hoàn tất, không hợp lệ và đã hủy.
- Hồ sơ cập nhật gần đây.
- Thống kê theo trạng thái và loại hồ sơ.

Admin, Thư ký và Nhân viên xem thống kê toàn hệ thống. Báo cáo vẫn chỉ dành cho
Admin và Thư ký.

## 5. Dữ liệu sinh viên

Hồ sơ tham chiếu sinh viên qua `student_code`. Thông tin sinh viên hiện hành:

- Mã sinh viên.
- Họ và tên.
- Ngày sinh, số điện thoại và email nếu có.

MVP chỉ dùng dữ liệu sinh viên hiện có để tra cứu và gắn hồ sơ. Không tự thêm
chức năng tạo/sửa/xóa sinh viên khi chưa có yêu cầu riêng.

## 6. Quản lý hồ sơ

### 6.1. Danh sách

Hiển thị tối thiểu:

- Mã hồ sơ.
- Sinh viên.
- Loại hồ sơ.
- Trạng thái.
- Ngày nộp.
- Ngày cập nhật.

Hỗ trợ search, filter, pagination, allowlisted sorting và truy cập chi tiết.

### 6.2. Tìm kiếm và bộ lọc

- Tìm theo mã hồ sơ hoặc mã/tên sinh viên.
- Lọc theo loại hồ sơ, trạng thái và khoảng ngày nộp.
- Có chức năng xóa bộ lọc.
- Search/filter/pagination xử lý phía backend.

### 6.3. Tạo hồ sơ

Admin và Thư ký được tạo hồ sơ. Input nghiệp vụ gồm:

- Mã hồ sơ.
- Sinh viên hiện có.
- Loại hồ sơ đang hoạt động.
- Trạng thái khởi tạo `waiting_for_receipt`.
- Ghi chú nếu có.

Hệ thống tự lưu ngày nộp và ngày cập nhật. Mã hồ sơ là duy nhất và không được
đổi sau khi tạo.

### 6.4. Chi tiết và cập nhật

Trang chi tiết hiển thị thông tin sinh viên, loại hồ sơ, trạng thái, ngày nộp,
ngày hoàn tất, lý do không hợp lệ, ghi chú và lịch sử trạng thái.

Admin, Thư ký và Nhân viên có thể cập nhật trong phạm vi Policy. Các thay đổi
quan trọng phải được ghi audit. Lịch sử trạng thái chỉ được tạo bởi nghiệp vụ
đổi trạng thái và không được sửa trực tiếp.

## 7. Xử lý hồ sơ theo vai trò và trạng thái

Hồ sơ không được phân công cho một người dùng cụ thể. Không có thao tác phân
công hoặc nhận hồ sơ. Cột schema `assigned_secretary_user_id` vẫn tồn tại nhưng
ứng dụng không đọc, ghi hoặc lọc theo cột này.

- Mọi vai trò nội bộ xem toàn bộ hồ sơ.
- Admin và Thư ký tạo hồ sơ; Nhân viên không tạo hồ sơ.
- Admin, Thư ký và Nhân viên chuyển trạng thái theo map đã duyệt.
- Nhân viên chỉ cập nhật ghi chú; Admin và Thư ký cập nhật MSSV, loại và ghi chú.

## 8. Trạng thái và workflow

Hệ thống dùng đúng bảy trạng thái hiện có:

| Code | Nhãn |
| --- | --- |
| `waiting_for_receipt` | Chờ tiếp nhận |
| `received` | Đã tiếp nhận |
| `processing` | Đang xử lý |
| `needs_supplement` | Cần bổ sung |
| `completed` | Hoàn tất |
| `invalid` | Không hợp lệ |
| `cancelled` | Đã hủy |

Transition hợp lệ:

- `waiting_for_receipt → received | cancelled`
- `received → processing | invalid | cancelled`
- `processing → needs_supplement | completed | invalid | cancelled`
- `needs_supplement → processing | cancelled`
- `completed`, `invalid`, `cancelled` là trạng thái kết thúc.

Mọi transition phải:

- Được Service kiểm tra và backend authorization cho phép.
- Cập nhật hồ sơ và thêm `document_status_history` trong một transaction.
- Lưu actor, trạng thái mới, thời gian, ghi chú và lý do không hợp lệ khi cần.
- Ghi audit event.

`completed_at` chỉ có giá trị khi trạng thái là `completed`. `invalid_reason` là
bắt buộc khi trạng thái là `invalid` và phải null ở trạng thái khác.

## 9. Lịch sử trạng thái

Lịch sử hiển thị:

- Trạng thái.
- Người thực hiện.
- Thời gian.
- Ghi chú.
- Lý do không hợp lệ nếu có.

Lịch sử là append-only và không có chức năng sửa/xóa qua ứng dụng.

## 10. Quản lý loại hồ sơ

Admin được:

- Xem danh sách loại hồ sơ.
- Thêm và chỉnh sửa loại hồ sơ.
- Bật/tắt trạng thái sử dụng.

Loại đã được hồ sơ tham chiếu không bị xóa trực tiếp. Loại inactive vẫn hiển
thị trên hồ sơ cũ nhưng không được chọn cho hồ sơ mới.

`document_statuses` là danh mục hệ thống phục vụ hiển thị bảy trạng thái đã
duyệt; không dùng để tạo thêm workflow/status ngoài Requirement.

## 11. Quản lý người dùng

Chỉ Admin được:

- Xem và tìm danh sách người dùng.
- Tạo/cập nhật tài khoản.
- Gán một trong ba role đã duyệt.
- Khóa/mở khóa tài khoản.
- Reset password nội bộ.

Danh sách hiển thị họ tên, username, role, trạng thái và ngày tạo. Không trả
password hash hoặc dữ liệu nhạy cảm. Mọi thay đổi role, trạng thái và password
phải được audit.

## 12. Báo cáo

Admin và Thư ký xem báo cáo:

- Tổng số hồ sơ.
- Hồ sơ theo trạng thái và loại.
- Hồ sơ tạo mới theo khoảng ngày nộp.
- Hồ sơ hoàn tất theo khoảng ngày hoàn tất.

Báo cáo lọc theo ngày, loại và trạng thái. Xuất XLSX/PDF không thuộc MVP.

## 13. Audit Log

Audit các hành động:

- Đăng nhập.
- Tạo/cập nhật hồ sơ.
- Thay đổi trạng thái.
- Tạo/cập nhật tài khoản.
- Đổi role, khóa/mở khóa và reset password.
- Tạo/cập nhật/bật-tắt loại hồ sơ.

Audit phải có actor, action/event, subject, thời gian và metadata đã allowlist.
Không log password, session/cookie, token, secret hoặc dữ liệu cá nhân không cần
thiết. Chỉ Admin được xem; không có chức năng sửa/xóa.

## 14. Giao diện

- Blade admin layout gồm sidebar, topbar, breadcrumb và main content.
- Menu: Tổng quan, Hồ sơ, Loại hồ sơ, Người dùng, Báo cáo, Nhật ký hoạt động.
- Menu và action hiển thị theo role để hỗ trợ UX; backend vẫn enforce quyền.
- Bảng có search, filter, pagination, loading, empty, error và row-action state.
- Form có label, required marker, CSRF, validation tại field và Lưu/Hủy.
- Responsive cho desktop, tablet và mobile; ưu tiên desktop.

## 15. Kiến trúc backend

Luồng bắt buộc:

`Controller → Form Request → Service → Repository → Model`

- Controller mỏng, chỉ điều phối HTTP và gọi Service.
- Form Request validate và authorize input HTTP.
- Service sở hữu business rule, transition và transaction.
- Repository là persistence/query boundary.
- Model khai báo entity, relationship, cast và fillable/guarded.
- Controller không query database hoặc gọi Model/Repository trực tiếp.
- Không đặt business logic lớn trong Controller, Model hoặc Blade.

## 16. Response và xử lý lỗi

Blade web routes dùng redirect, flash message và validation error bag. Không tạo
JSON API chỉ để phục vụ Blade.

Endpoint `/api/*` thực tế, nếu được phê duyệt, dùng format:

```json
{
    "success": true,
    "message": "Thao tác thành công",
    "data": {}
}
```

```json
{
    "success": false,
    "message": "Có lỗi xảy ra",
    "errors": {}
}
```

HTTP status dùng phù hợp: 200, 201, 400, 401, 403, 404, 422, 500. Exception
được render tập trung; production không trả stack trace, SQL error, server path,
secret hoặc thông tin nội bộ.

Public JSON API tra cứu hồ sơ theo MSSV cho mobile được mô tả tại
`docs/PUBLIC-STUDENT-DOCUMENTS-API.md`.

## 17. Bảo mật và dữ liệu

- Backend kiểm tra authentication, active account và authorization cho mọi
  protected action.
- Validate toàn bộ input; escape output Blade.
- Dùng query scope để tránh rò dữ liệu ngoài quyền.
- Không dùng dữ liệu thật trong fixture, CI hoặc test.
- Không commit `.env`, credential, private SQL hoặc dữ liệu sinh viên.
- Development, test và database dữ liệu thật phải tách biệt.
- Database dữ liệu thật chỉ được truy cập bằng application account có quyền tối
  thiểu; không dùng root.

## 18. Ngoài phạm vi MVP

- Upload/download/xóa tài liệu đính kèm.
- Email và notification qua email.
- Quản lý CRUD sinh viên.
- XLSX/PDF export.
- API-first/SPA/mobile app.
- Permission/assignment table mới.
- Tích hợp dịch vụ ngoài, workflow engine hoặc search engine.

Mọi thay đổi phạm vi trên yêu cầu một quyết định mới và schema change được phê
duyệt riêng khi cần.

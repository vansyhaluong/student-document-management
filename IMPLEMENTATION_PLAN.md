# IMPLEMENTATION PLAN — Hệ thống quản lý tiếp nhận hồ sơ sinh viên

> Trạng thái: Draft for Review  
> Ngày lập: 2026-07-29  
> Nguồn: `REQUIREMENTS.md`, `ARCHITECTURE.md`, private local `student_document_management.sql`  
> Phạm vi: Lập kế hoạch, chưa triển khai source code

## 1. Mục tiêu

Tạo Laravel modular monolith cho khoa CNTT theo dependency chain:

```text
Controller → Service → Repository → Model
```

Controller sử dụng Form Request để authorize và validate. Hệ thống phải tái hiện đúng schema, workflow, quyền, lịch sử trạng thái, báo cáo và convention đã chốt.

Kế hoạch này không tự giải quyết các open question. `Public Submission` và `Public Lookup` là **implementation-blocked** cho đến khi decision về chống dò/khai thác dữ liệu công khai được phê duyệt.

## 2. Kết quả hoàn thành mong đợi

- Laravel 13.x chạy trong dải PHP 8.3–8.5; development và CI cùng cố định PHP 8.4.
- Migration tái hiện đúng baseline SQL, constraint, index và trigger.
- Các module nội bộ hoạt động qua Blade với session authentication và server-side authorization.
- Mọi transition đúng matrix; current state và history được ghi nguyên tử.
- Repository là boundary bắt buộc; Service không gọi Eloquent/Query Builder trực tiếp.
- Lỗi và response tuân chuẩn Architecture.
- Báo cáo trả đủ bảy trạng thái và dùng biên thời gian UTC nửa mở.
- Integration test chạy trên MariaDB thật cho hành vi phụ thuộc constraint/locking.
- Hai module public chỉ được hoàn thành sau khi decision gate tương ứng được mở.

## 3. Fact, giả định và giới hạn

### 3.1 Fact

- Repository hiện chỉ có tài liệu và SQL baseline, chưa có Laravel application.
- Schema có năm bảng: `students`, `document_types`, `student_documents`, `document_status_history`, `users`.
- UI chính là Blade; JSON chỉ dùng cho AJAX/API nội bộ có nhu cầu rõ.
- User và document type không hard delete; Student chỉ xóa khi chưa liên kết.
- NFR định lượng và một số chính sách production vẫn chưa chốt.
- File SQL hiện có chứa dữ liệu Sinh viên thật là private input, không phải automated-test fixture và không được đưa vào Git/CI artifact.

### 3.2 Giả định an toàn cho việc lập plan

- Laravel application được tạo ngay tại repository hiện tại, không tạo thêm repository con.
- Dùng PHPUnit theo bộ test mặc định của Laravel; có thể đổi sang Pest trước khi bắt đầu viết test mà không đổi kiến trúc.
- Automated test chỉ dùng factory/fixture giả. Metadata comparison dùng schema-only/sanitized baseline không chứa dữ liệu của 8.145 Sinh viên.
- Private import path nằm ngoài Git và được truyền qua environment/config; CI không có quyền truy cập path này.
- Không triển khai public route dưới dạng tạm thời hoặc giảm bảo mật để vượt decision gate.

### 3.3 Không thuộc MVP

- Mobile app, public API cho bên thứ ba, quản lý nhiều khoa/lớp.
- Tự xác định tình trạng học, tự phát hiện hồ sơ trùng, phê duyệt ngoài hệ thống.
- Microservices, event bus, queue hoặc scheduler khi chưa có use case.

## 4. Decision gates

| Gate | Quyết định cần có | Ảnh hưởng | Trạng thái |
|---|---|---|---|
| DG-001 | Cơ chế chống dò/khai thác cho Public Submission và Public Lookup | Chặn toàn bộ implementation hai module public | **Blocked** |
| DG-002 | Thuật toán/tham số mật khẩu, session lifetime, lockout và reset password | Chặn production sign-off của Identity & Access | Pending |
| DG-003 | Cách bootstrap Admin đầu tiên | Chặn deploy môi trường đầu tiên | Pending |
| DG-004 | Phiên bản MariaDB production | Chặn production migration rehearsal | Pending |
| DG-005 | NFR-001 đến NFR-007 | Chặn performance/availability/accessibility acceptance | Pending |
| DG-006 | Có cần idempotency token ngoài cơ chế chống double-click hay không | Ảnh hưởng Public Submission | Pending, phụ thuộc DG-001 |

## 5. Nguyên tắc thực hiện

- Mỗi task chỉ bắt đầu khi dependency và Definition of Ready của task đã đạt.
- Integration test được ưu tiên cho persistence, transaction, authorization và workflow.
- Test constraint/ENUM/trigger/locking phải chạy MariaDB, không thay bằng SQLite.
- Mỗi thay đổi schema có migration và test; không sửa database thủ công.
- Không tạo `BaseRepository`, `CommonService` hoặc controller gọi Model trực tiếp.
- Không expose route trước khi authorization, validation và test tương ứng hoàn tất.
- Không trộn refactor ngoài phạm vi vào task nghiệp vụ.

## 6. Thứ tự triển khai tổng thể

```text
P0 Decision and environment gates
  → P1 Laravel foundation
  → P2 Database and persistence
  → P3 Shared application infrastructure
  → P4 Identity and access
  → P5 Administration and directories
  → P6 Internal document workflow
  → P7 Reporting
  → P8 Public modules [BLOCKED by DG-001]
  → P9 System verification and deployment readiness
```

P4–P7 có thể được lập trình khi DG-001 còn blocked. P8 và end-to-end public MVP release không được bắt đầu cho đến khi DG-001 được mở.

## 7. Phase P0 — Decision và environment gates

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P0-01 | Kiểm tra Laravel 13.x, PHP 8.4 cố định, Composer và extension PDO MySQL, intl, mbstring, openssl | Không | Ready | Development và CI cùng báo đúng PHP 8.4; runtime policy cho phép 8.3–8.5 |
| P0-02 | Chuẩn bị MariaDB test riêng và xác minh capability UTC/strict mode | P0-01 | Ready | Server hỗ trợ session `time_zone = '+00:00'` và SQL mode `STRICT_TRANS_TABLES`; chưa kết luận Laravel connection đã áp dụng |
| P0-03 | Tạo schema-only/sanitized baseline từ private SQL và import vào MariaDB test sạch | P0-02 | Ready | Không chứa row Sinh viên thật; metadata đủ table/index/FK/CHECK/trigger |
| P0-04 | Thiết lập policy private import ngoài Git/CI | P0-03 | Ready | Private path được ignore/kiểm tra chống commit; CI chỉ dùng sanitized baseline và fake data |
| P0-05 | Ghi decision log cho DG-002 đến DG-005 | Người phụ trách quyết định | Pending | Mỗi gate có owner, deadline và quyết định được phê duyệt |
| P0-06 | Nghiên cứu và chốt DG-001 | Security/Product owner | **Ready — decision/research only** | Threat model và cơ chế được duyệt; chỉ task này được chạy cho public modules |

Exit criteria P0 cho phần nội bộ: P0-01 đến P0-04 đạt, sanitized baseline không chứa dữ liệu thật, MariaDB server/test environment xác nhận có khả năng dùng UTC và `STRICT_TRANS_TABLES`. Việc Laravel connection thực sự áp dụng hai cấu hình thuộc P1. P0-06 chỉ là exit criteria của P8.

## 8. Phase P1 — Laravel foundation

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P1-01 | Tạo Laravel 13.x application tại repository root, pin PHP 8.4 cho development | P0-01 | Ready | App boot thành công trên PHP 8.4 |
| P1-02 | Cấu hình `.env.example`, MariaDB connection UTC, strict SQL mode, locale và timezone hiển thị | P1-01, P0-02 | Ready | Mỗi DB session có `time_zone = '+00:00'` và SQL mode chứa `STRICT_TRANS_TABLES` |
| P1-03 | Tạo cấu trúc namespace theo Architecture | P1-01 | Ready | Có Enums, Exceptions, Requests, Services, Repositories, Policies, Support |
| P1-04 | Cấu hình code style và static analysis phù hợp PHP/Laravel | P1-01 | Ready | Lệnh format/lint chạy thành công |
| P1-05 | Tạo test bootstrap dùng MariaDB test và guard chống nhầm database | P1-02 | Ready | Test từ chối chạy nếu database không mang tên/phân loại test |
| P1-06 | Thiết lập và chạy xanh GitHub Actions workflow cho PR vào `develop`/`main` | P1-04, P1-05 | Ready | PHP 8.4; lint, static analysis, unit, feature, MariaDB integration và `migrate:fresh` chạy xanh; ghi nhận chính xác check names từ workflow run |
| P1-07 | Cấu hình GitHub Ruleset cho `main` và `develop` | P1-06 | Ready sau khi workflow xanh | Chỉ thêm đúng check names đã xuất hiện trong run xanh; required status checks áp cho PR vào cả hai branch |

Exit criteria P1: application boot/config cache đạt trên PHP 8.4; integration check chứng minh Laravel connection thực tế có `time_zone = '+00:00'` và SQL mode chứa `STRICT_TRANS_TABLES`; P1-06 workflow đã xanh trước, sau đó P1-07 Ruleset dùng đúng check names cho `develop`/`main`.

## 9. Phase P2 — Database và persistence

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P2-01 | Chuyển năm bảng baseline thành Laravel migrations | P1-05, P0-03 | Ready | Schema migration khớp tên/kiểu/default/PK |
| P2-02 | Thêm index, FK theo `fk_<child_table>_<foreign_key_column>`, CHECK và trigger bất biến mã hồ sơ | P2-01 | Ready | So sánh metadata với baseline SQL |
| P2-03 | Tạo Eloquent Models, casts, relationships và timestamp mapping | P2-01 | Ready | Model integration test đọc/ghi đúng schema |
| P2-04 | Tạo enums `DocumentStatus`, `UserRole` và mapping Model | P2-03 | Ready | Bảy status và ba role round-trip đúng |
| P2-05 | Tạo Repository contracts và Eloquent implementations theo use case | P2-03 | Ready | Service test có thể mock contract; integration test dùng implementation thật |
| P2-06 | Tạo factories/sanitized fixtures cho Student, Document Type, User và Document | P2-01 | Ready | Test data tối thiểu theo case, không chứa row thật hoặc snapshot 8.145 Sinh viên |
| P2-07 | Tạo private import command đọc path ngoài Git | P2-01, P0-04 | Ready, không chạy trong CI | Validate file/input; import có audit; CI không có secret/path và không chạy command |

Integration tests bắt buộc:

- `invalid_reason` đúng invariant hai chiều ở cả current/history.
- `completed_at` có giá trị khi và chỉ khi status là `completed`.
- `document_code` unique và trigger từ chối update.
- FK từ chối xóa Student có hồ sơ và từ chối mất liên kết history.
- Độ dài `note`/`invalid_reason` khớp schema.
- MariaDB session integration test fail nếu thiếu `STRICT_TRANS_TABLES`.
- CI scan/test xác nhận không có dữ liệu thật hoặc private import file trong repository/artifact.

Exit criteria P2: `migrate:fresh` và rollback/rebuild trên MariaDB test thành công; metadata đối chiếu với sanitized baseline; factories không chứa dữ liệu thật; private import command/path không được CI tải hoặc chạy.

## 10. Phase P3 — Shared application infrastructure

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P3-01 | Tạo `Clock` contract và `SystemClock` | P1-03 | Ready | Test đóng băng thời gian không cần test-only hook trong production class |
| P3-02 | Tạo exception taxonomy và mapping trong `bootstrap/app.php` | P1-03 | Ready | Blade/JSON trả đúng status/behavior đã chốt |
| P3-03 | Tạo `RequestTrace` middleware và `ApiResponse` | P3-02 | Ready | Response/log có cùng trace ID, không lộ stack trace |
| P3-04 | Tạo shared Blade components cho flash/errors | P3-02 | Ready | PRG hiển thị `success`, `warning`, `error` nhất quán |
| P3-05 | Đăng ký Repository bindings trong `AppServiceProvider` | P2-05 | Ready | Container resolve đúng interface |
| P3-06 | Tạo policy/role infrastructure và route groups nội bộ | P2-04 | Ready | Route nội bộ mặc định yêu cầu auth và server-side role check |
| P3-07 | Tạo Security Headers middleware | P1-03 | Ready | Feature test kiểm tra CSP/frame policy, content-type protection, referrer policy và HSTS chỉ trên production HTTPS |
| P3-08 | Tạo application liveness và database readiness health checks | P1-02, P2-01 | Ready | DB check dùng query nhẹ; status tách biệt; response không lộ DSN, credential hoặc SQL error |

Exit criteria P3: exception/response/trace/authorization foundation, security headers và hai loại health check có feature/integration test trước khi module dùng.

## 11. Phase P4 — Identity & Access

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P4-01 | Ánh xạ `User` Authenticatable với `password_hash` và `is_active` | P2-03 | Ready | Hash login đúng; inactive user bị từ chối |
| P4-02 | Tạo Login Form Request, Auth Service, Controller và Blade view | P3-02, P3-06, P4-01 | Ready for non-production baseline | Login regenerate session; sai credential không tạo session |
| P4-03 | Tạo logout flow | P4-02 | Ready | Invalidate session và regenerate CSRF token |
| P4-04 | Áp role middleware/Gates/Policies cho Staff, Thư ký, Admin | P4-02 | Ready | Direct URL/action trái quyền trả đúng 403/redirect |
| P4-05 | Hoàn thiện password/session/lockout/reset theo DG-002 | DG-002 | Pending | Security acceptance đạt |
| P4-06 | Tạo quy trình bootstrap Admin theo DG-003 | DG-003 | Pending | Không commit password; audit được lần bootstrap |

Exit criteria P4 cho phát triển nội bộ: P4-01 đến P4-04 đạt. Production sign-off cần P4-05 và P4-06.

## 12. Phase P5 — Administration và directories

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P5-01 | Quản lý User qua Controller/Form Request/Service/Repository | P4-04 | Ready | Create/read/update/activate/deactivate; không có destroy route |
| P5-02 | Quản lý Document Type | P4-04 | Ready | Create/read/update/activate/deactivate; không hard delete |
| P5-03 | Quản lý Student | P4-04 | Ready | CRUD; chỉ xóa khi chưa liên kết hồ sơ |
| P5-04 | Tạo search/filter/pagination cho ba màn hình | P5-01, P5-02, P5-03 | Ready | Query qua Repository, giữ filter khi phân trang |

Integration/feature tests bắt buộc:

- Không tồn tại endpoint hard delete User/Document Type.
- Khóa User không làm mất history; document type inactive không mất hồ sơ.
- Student có liên kết bị từ chối xóa; Student chưa liên kết xóa được.
- Role ngoài Admin không gọi trực tiếp được action quản trị.

Exit criteria P5: AC-FR-009 và AC-FR-010 đạt cho phần nội bộ.

## 13. Phase P6 — Internal document workflow

### 13.1 State transition matrix

Side effect chung của mỗi transition thành công: lock current row; cập nhật current status/note/reason/assignment/completed time; tạo đúng một immutable history với cùng `$now`. Transition bị từ chối không cập nhật current row và không tạo history.

| Current status | Next status | Allowed role | Requirement IDs | Side effects riêng |
|---|---|---|---|---|
| `waiting_for_receipt` | `received` | Staff, Secretary | FR-015, FR-017, FR-022, BR-005, BR-006 | History đầu tiên; không thay đổi assignment; Staff dùng note `NULL` |
| `waiting_for_receipt` | `cancelled` | Secretary | FR-021, FR-022, BR-005, BR-006 | Reason/completed time `NULL` |
| `received` | `processing` | Secretary | FR-018, FR-022, BR-005, BR-006 | Gán actor nếu assignment đang `NULL`; không ghi đè assignment có sẵn |
| `received` | `cancelled` | Secretary | FR-021, FR-022, BR-005, BR-006 | Giữ assignment; reason/completed time `NULL` |
| `processing` | `needs_supplement` | Secretary | FR-018, FR-022, BR-005, BR-006 | Giữ assignment; reason/completed time `NULL` |
| `processing` | `completed` | Secretary | FR-018, FR-020, FR-022, BR-005, BR-006, BR-008 | `completed_at = $now`; reason `NULL` |
| `processing` | `invalid` | Secretary | FR-018, FR-022, BR-005, BR-006, BR-009 | Reason sau trim bắt buộc 1–200 ký tự; completed time `NULL` |
| `processing` | `cancelled` | Secretary | FR-021, FR-022, BR-005, BR-006 | Giữ assignment; reason/completed time `NULL` |
| `needs_supplement` | `processing` | Secretary | FR-018, FR-022, BR-005, BR-006 | Giữ assignment, không tự gán lại |
| `needs_supplement` | `cancelled` | Secretary | FR-021, FR-022, BR-005, BR-006 | Giữ assignment; reason/completed time `NULL` |
| `completed` | Không có | Không role nào | BR-005 | Terminal; từ chối và không tạo history |
| `invalid` | Không có | Không role nào | BR-005 | Terminal; từ chối và không tạo history |
| `cancelled` | Không có | Không role nào | BR-005 | Terminal; từ chối và không tạo history |

Staff và Secretary đều được phép thực hiện `waiting_for_receipt → received`. Bốn transition từ trạng thái chưa kết thúc sang `cancelled` được xác nhận bởi FR-021 và BR-005. Staff không được thực hiện transition nào khác. Mọi cặp current/next không có trong matrix đều bị từ chối.

### 13.2 Implementation tasks

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P6-01 | Tạo list/detail/history query cho Staff và Thư ký | P2-05, P4-04 | Ready | Filter đúng student code, họ tên, type, status, submitted date |
| P6-02 | Tạo `DocumentWorkflowService` với injected `Clock` và state matrix | P2-05, P3-01, P4-04 | Ready, song song P6-01 | Không gọi thời gian trực tiếp; một `$now` cho current/history |
| P6-03 | Triển khai transaction, `lockForUpdate()` và history insert | P6-02 | Ready | Current/history cùng commit hoặc cùng rollback |
| P6-04 | Triển khai note/current snapshot semantics | P6-03 | Ready | Current note reset NULL nếu transition mới không có note; history cũ bất biến |
| P6-05 | Triển khai assignment semantics | P6-03 | Ready | Chỉ gán received → processing khi chưa có; không tự ghi đè |
| P6-06 | Triển khai invalid/completed side effects | P6-03 | Ready | Invariant reason và completed time đúng matrix |
| P6-07 | Tạo shared `DocumentReceptionController`, `ReceiveStudentDocumentRequest` và receive use case | P6-01, P6-03 | Ready | Route `/internal/documents/{document}/receive` nằm ngoài Staff-only namespace/group; cả Staff và Secretary gọi cùng endpoint/use case |
| P6-08 | Tạo Secretary Form Request/Controller cho các transition ngoài receive | P6-01, P6-03, P6-04, P6-05, P6-06 | Ready | Authorization/validation trước Controller; đủ action Secretary còn lại theo matrix |
| P6-09 | Xây đầy đủ Blade UI cho Staff | P3-04, P6-07 | Ready | List/filter/detail/history/receive UI; responsive states và feature tests |
| P6-10 | Xây đầy đủ Blade UI cho Secretary | P3-04, P6-07, P6-08 | Ready | List/filter/detail/history/receive/transition/note/reason UI; receive submit cùng shared endpoint với Staff |

State matrix tests bắt buộc:

- Test từng transition hợp lệ với đúng role.
- Test mọi transition không có trong matrix bị từ chối và không tạo history.
- Test ba terminal state không chuyển tiếp.
- Test Staff bị từ chối ở mọi transition ngoài waiting → received.
- Test cả Staff và Secretary đều thực hiện được waiting → received.
- Feature test xác nhận shared receive endpoint không bị giới hạn bởi Staff namespace/group và vẫn từ chối role khác.
- Test reason rỗng/whitespace/quá 200 và reason ở non-invalid đều bị từ chối.
- Test note 500 ký tự hợp lệ, 501 ký tự bị từ chối.
- Test cùng một `$now` cho `completed_at` và `changed_at` khi completed.
- Test hai request đồng thời không gây lost update hoặc tạo lịch sử sai.
- Feature test đầy đủ Blade UI Staff/Secretary: route, filter, empty/error state, validation, flash, authorization và submit transition.

Exit criteria P6: P6-01 và P6-02 có thể hoàn thành song song; P6-03 đến P6-10 đạt; AC-FR-006, AC-FR-007, AC-FR-008 và Blade UI Staff/Secretary đều qua feature/integration tests trên MariaDB.

## 14. Phase P7 — Reporting

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P7-01 | Tạo Report Repository query đủ bảy trạng thái và theo loại | P2-05, P4-04 | Ready | Kết quả luôn có đủ bảy key, kể cả count bằng 0 |
| P7-02 | Tạo Report Service chuyển biên local date/month sang UTC | P3-01, P7-01 | Ready | Dùng khoảng nửa mở trên `submitted_at` |
| P7-03 | Tạo Admin Report Controller/Form Request/Blade view | P7-02 | Ready | Chỉ Admin truy cập; filter ngày/tháng hợp lệ |
| P7-04 | Tối ưu query/index theo dữ liệu đo | P7-03, DG-005 | Pending NFR | Explain plan và response time đạt ngưỡng đã duyệt |

Integration tests bắt buộc:

- Trả đủ `waiting_for_receipt`, `received`, `processing`, `needs_supplement`, `completed`, `invalid`, `cancelled`.
- Record đúng tại biên đầu ngày, sát biên cuối và đầu ngày kế tiếp theo `Asia/Ho_Chi_Minh`.
- Tháng dùng `[monthStartUtc, nextMonthStartUtc)` và không double-count.
- Role ngoài Admin bị từ chối.

Exit criteria P7 chức năng: AC-FR-011 đạt. Performance acceptance chờ DG-005.

## 15. Phase P8 — Public Submission và Public Lookup

> **Toàn bộ phase P8 implementation-blocked bởi DG-001.** Không tạo route, Controller, Form Request, Service hoặc Blade view trước khi gate được mở.

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P8-00 | Hoàn thành threat model và phê duyệt cơ chế chống dò | Không | **Ready — decision/research only** | Decision record mở DG-001 sau khi Product/Security duyệt |
| P8-01 | Cập nhật Requirements/Architecture theo decision | P8-00 | **Blocked** | Acceptance criteria public có thể kiểm thử |
| P8-02 | Triển khai `DocumentCodeGenerator` thuần với injected Clock/random source | P8-01, P3-01 | **Blocked** | Chỉ trả candidate code đúng alphabet/ngày; không Repository/insert/catch/retry |
| P8-03 | Triển khai Student verification và active document types | P8-01, P2-05, P2-06 | **Blocked** | Chỉ phụ thuộc Repository và dữ liệu giả/sanitized cần thiết; không lộ dữ liệu ngoài policy |
| P8-04 | Triển khai `StudentDocumentService` create/insert/collision retry | P8-02, P2-05 | **Blocked** | Không pre-check; chỉ retry đúng unique constraint tối đa 5; lỗi khác ném lại |
| P8-05 | Triển khai Public Submission Form Request/Controller/Blade | P8-03, P8-04 | **Blocked** | Tạo waiting document, không tạo history; redirect bằng `document_code`, không dùng ID |
| P8-06 | Triển khai Public Lookup | P8-01, P2-05 | **Blocked** | Chỉ trả trường FR-009; không expose history/API ngoài phạm vi |
| P8-07 | Áp rate limit, audit và control theo DG-001 | P8-03, P8-05, P8-06 | **Blocked** | Abuse/security tests đạt |
| P8-08 | Chốt và áp dụng quyết định idempotency | P8-00, DG-006 | **Blocked/Pending** | Nếu cần token: implement và test; nếu không cần: lưu rationale/compensating controls và test hành vi submit lặp theo quyết định |

Tests sau khi gate mở:

- Mã hồ sơ đúng regex, alphabet và ngày `Asia/Ho_Chi_Minh`.
- Unit test generator chứng minh không có persistence/retry side effect.
- Integration test Service chứng minh collision chỉ retry khi lỗi đúng `uq_student_documents_document_code`; lỗi khác được ném lại; lần thứ 5 thất bại tạo exception chuẩn.
- Redirect/result route dùng `document_code`, không lộ primary key tuần tự.
- Public Submission tạo `student_documents` ở `waiting_for_receipt` nhưng không tạo history; Staff hoặc Secretary receive mới tạo history đầu tiên.
- Submission cùng loại vẫn tạo hồ sơ mới theo BR-003.
- Public response không trả lịch sử và không vượt tập trường được duyệt.
- Rate limit và cơ chế DG-001 chống enumeration theo acceptance criteria mới.

Exit criteria P8: DG-001/DG-006 đã chốt, REQUIREMENTS được cập nhật, P8-01 đến P8-08 đạt, AC-FR-001 đến AC-FR-004 và AC-SEC-001 đạt; submission không có history trước lần receive.

## 16. Phase P9 — System verification và deployment readiness

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P9-01 | Chạy full unit/feature/integration suite qua local và GitHub Actions | P1-07, P4–P7; P8 nếu gate mở | Ready khi phases hoàn tất | Không có required check fail/flaky chưa xử lý |
| P9-02 | Security review auth, authorization, CSRF, security headers, public surface, log redaction | P3-07, P4, P6, P8 nếu public release | Pending dependencies | AC-SEC-001, header tests và decision controls đạt |
| P9-03 | Migration rehearsal trên bản sao dữ liệu thật trong môi trường private | P0-04, DG-004, P2 | Pending | Không chạy trên CI, không upload cache/artifact/log chứa dữ liệu; access có kiểm soát; backup/restore/rollback đạt; có retention deadline và bằng chứng xóa bản sao sau diễn tập |
| P9-04 | Kiểm thử NFR | DG-005, functional complete | Pending | Mỗi NFR có điều kiện đo và ngưỡng đạt |
| P9-05 | Build/deploy rehearsal | P3-08, P9-01, P9-02, P9-03 | Pending | Config/route/view cache, liveness, DB readiness và rollback hoạt động |
| P9-06 | UAT theo acceptance criteria | Phạm vi release hoàn tất | Pending | Biên bản nghiệm thu và known issues |

Không được gọi bản release là public MVP hoàn chỉnh khi P8 còn blocked. Khi đó chỉ có thể nghiệm thu bản nội bộ/pre-public.

Exit criteria P9: CI required checks xanh trên `develop`/`main`; private migration rehearsal đạt và bản sao dữ liệu được xóa theo policy; security headers và health checks đạt; UAT/NFR được ghi rõ `Đạt` hoặc `Chưa thể kiểm thử` theo decision gates.

## 17. Test strategy và quality gates

### 17.1 Test pyramid áp dụng

- Unit: pure state rules, generator, Clock conversion và exception mapping nhỏ.
- Feature: route, Form Request, Blade redirect/flash, authentication và authorization.
- Integration ưu tiên: Repository, MariaDB constraint, transaction, locking, report query và deletion behavior.
- End-to-end: các workflow chính sau khi từng module qua feature/integration tests.

### 17.2 Quality gate cho mỗi pull request

- Format/lint/static analysis đạt.
- Test mới bao phủ hành vi user-facing hoặc invariant thay đổi.
- Migration được test trên MariaDB fresh database.
- Không có Controller gọi Model/DB hoặc Service gọi Eloquent/Query Builder.
- Không log credential, cookie, full note hoặc full invalid reason.
- Route mới có Form Request và authorization test.
- Không thay đổi public blocked status nếu chưa có decision record DG-001.
- GitHub Actions trên PR vào `develop`/`main` phải chạy lint, static analysis, unit, feature, MariaDB integration và `migrate:fresh` bằng PHP 8.4.
- Chỉ cấu hình Ruleset sau khi workflow đã có một run xanh; required status checks phải dùng đúng check names ghi nhận từ run đó.
- CI không tải, cache hoặc artifact private import/dữ liệu 8.145 Sinh viên.

## 18. Traceability triển khai

| Nhóm requirement | Phase chính | Verification |
|---|---|---|
| FR-001 đến FR-009, SEC-006 | P8 | Blocked bởi DG-001; AC-FR-001 đến AC-FR-004 |
| FR-010, FR-011, SEC-001 đến SEC-005 | P3-07, P4 | Auth/role/header feature tests, AC-FR-005, AC-SEC-001 |
| FR-012 đến FR-022, BR-003 đến BR-009 | P6 | State matrix, Staff/Secretary Blade UI, transaction/concurrency tests |
| FR-023 đến FR-026, BR-011 | P5 | Admin CRUD/activation/deletion tests |
| FR-027, FR-028, BR-012 | P7 | Seven-status và UTC boundary tests |
| BR-010 | P8-02, P8-04, P8-05 | Pure generator, Service collision retry, unique/immutable/redirect tests |
| DR-001 đến DR-007 | P0-03, P2 | Sanitized metadata baseline, migrations và MariaDB integration tests |
| Runtime/CI/operations | P0-01, P1-02, P1-06, P1-07, P3-08 | PHP 8.4, strict UTC DB session, required CI checks và health tests |
| NFR-001 đến NFR-007 | P9-04 | Chờ DG-005 |

## 19. Rủi ro và kiểm soát

| Rủi ro | Tín hiệu sớm | Kiểm soát |
|---|---|---|
| Public enumeration/data exposure | Có đề xuất triển khai route chỉ với rate limit chung | Giữ DG-001 hard gate; threat model và acceptance criteria trước code |
| Migration khác baseline | Metadata/index/constraint name lệch | P0 schema-only/sanitized baseline, P2 metadata comparison, MariaDB integration tests |
| Dữ liệu thật lọt vào Git/CI | Có row/mã Sinh viên thật trong fixture/artifact | External private path, ignore/scan gate, fake factories và sanitized baseline |
| Bản sao rehearsal tồn tại quá hạn | Không có owner/deletion evidence sau P9-03 | Private isolated environment, retention deadline, owner và verified deletion record |
| Lost update/history sai | Read-then-write ngoài transaction | Service-owned transaction và `lockForUpdate()` test đồng thời |
| Timezone lệch báo cáo/mã | Dùng `now()` trực tiếp hoặc `BETWEEN` cuối ngày | Inject Clock, DB UTC, half-open UTC boundaries |
| Repository thành CRUD abstraction rỗng | Xuất hiện `BaseRepository` | Review theo use case contract; quality gate cấm base generic |
| Quyền xóa bị mở rộng | Có destroy route User/Document Type | Route/feature test xác nhận 404/405 và Service policy |
| NFR bị coi là đạt khi chưa có ngưỡng | Báo cáo test không ghi điều kiện đo | DG-005 và AC-NFR-001 là production gate |

## 20. Definition of Ready và Done

Một task Ready khi:

- Requirement/AC và dependency đã rõ.
- Không bị decision gate block.
- Database/test fixture và quyền cần thiết đã có.
- Input/output, error và authorization behavior xác định được.

Một task Done khi:

- Code tuân dependency/convention Architecture.
- Unit/feature/integration test phù hợp đã đạt.
- Migration và rollback/rebuild được kiểm tra nếu có schema change.
- Traceability tới requirement/AC được cập nhật.
- Không còn warning/error chưa được ghi nhận.

## 21. Thứ tự bắt đầu đề xuất

1. P0-01 → P0-04 để pin PHP 8.4, tạo sanitized baseline và khóa private-data policy.
2. P1-01 → P1-07 để tạo Laravel/test/CI foundation và Ruleset từ check names của workflow đã xanh.
3. P2-01 → P2-07 và P3-01 → P3-08 để khóa schema, persistence, security headers và health checks.
4. P4, P5, P6, P7 theo dependency; P6-01 và P6-02 chạy song song.
5. Chạy P8-00 song song dưới dạng decision/research; không chạy P8-01 trở đi khi gate chưa mở.
6. P9 theo phạm vi release đã được phê duyệt.

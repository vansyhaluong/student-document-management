# IMPLEMENTATION PLAN — Hệ thống quản lý tiếp nhận hồ sơ sinh viên

> Trạng thái: Approved for Conditional Implementation — P0–P8; production gates pending
> Ngày lập: 2026-07-30
> Nguồn: `REQUIREMENTS.md`, `ARCHITECTURE.md`, private local `student_document_management.sql`
> Phạm vi: Lập kế hoạch Laravel + React/Inertia, chưa triển khai source code

## 1. Mục tiêu

Tạo Laravel modular monolith cho khoa CNTT theo dependency chain:

```text
Controller → Service → Repository → Model
```

Controller sử dụng Form Request để authorize và validate. Hệ thống phải tái hiện đúng schema, workflow, quyền, lịch sử trạng thái, báo cáo và convention đã chốt.

Kế hoạch này không tự giải quyết các open question còn lại. P0–P8 được phê duyệt để triển khai có điều kiện theo dependency. DG-001 đến DG-004 và DG-006 đã chốt; DG-005 vẫn Pending và bắt buộc chốt trước P9/production.

## 2. Kết quả hoàn thành mong đợi

- Laravel 13.x chạy trong dải PHP 8.3–8.5; development và CI cùng cố định PHP 8.4.
- Migration tái hiện đúng baseline SQL, constraint, index và trigger.
- Các module nội bộ hoạt động qua React + TypeScript/Inertia với session authentication và server-side authorization; Blade phục vụ public/error pages.
- Mọi transition đúng matrix; current state và history được ghi nguyên tử.
- Repository là boundary bắt buộc; Service không gọi Eloquent/Query Builder trực tiếp.
- Lỗi và response tuân chuẩn Architecture.
- Báo cáo trả đủ bảy trạng thái và dùng biên thời gian UTC nửa mở.
- Integration test chạy trên MariaDB thật cho hành vi phụ thuộc constraint/locking.
- Hai module public triển khai theo DG-001 và DG-006 đã duyệt: lookup `student_code`-only; submission chống retry/double-submit bằng bảng `public_submission_idempotency`, MariaDB transaction và unique constraint.
- MVP chạy một application instance, không dùng Redis; session cấu hình `SESSION_DRIVER=database`, `SESSION_EXPIRE_ON_CLOSE=true`, `SESSION_LIFETIME=120` và không có remember-me. Thiết kế không phụ thuộc process-local state để còn nâng cấp nhiều instance sau MVP.

## 3. Fact, giả định và giới hạn

### 3.1 Fact

- Repository hiện chỉ có tài liệu và SQL baseline, chưa có Laravel application.
- Baseline import có năm bảng nghiệp vụ: `students`, `document_types`, `student_documents`, `document_status_history`, `users`. Application bổ sung bảng `sessions` và `public_submission_idempotency` bằng migration.
- UI nội bộ dùng React + TypeScript qua Inertia; Blade dùng cho public/error pages; JSON chỉ dùng cho endpoint có nhu cầu rõ.
- User và document type không hard delete; Student chỉ xóa khi chưa liên kết.
- Các chỉ tiêu NFR định lượng tại DG-005 vẫn chưa chốt.
- File SQL hiện có chứa dữ liệu Sinh viên thật là private input, không phải automated-test fixture và không được đưa vào Git/CI artifact.

### 3.2 Giả định an toàn cho việc lập plan

- Laravel application được tạo ngay tại repository hiện tại, không tạo thêm repository con.
- React nằm trong cùng Laravel application và build bằng Vite; không tạo frontend repository hoặc auth token/API riêng cho MVP.
- Node.js/npm được pin bằng file quản lý phiên bản và `package.json#engines` khi tạo skeleton; CI dùng đúng phiên bản đã pin.
- Dùng PHPUnit theo bộ test mặc định của Laravel; có thể đổi sang Pest trước khi bắt đầu viết test mà không đổi kiến trúc.
- Automated test chỉ dùng factory/fixture giả. Metadata comparison dùng schema-only/sanitized baseline không chứa dữ liệu của 8.145 Sinh viên.
- Private import path nằm ngoài Git và được truyền qua environment/config; CI không có quyền truy cập path này.
- Không triển khai public route dưới dạng tạm thời hoặc giảm bảo mật để vượt decision gate.

### 3.3 Không thuộc MVP

- Mobile app, public API cho bên thứ ba, quản lý nhiều khoa/lớp.
- Tự xác định tình trạng học, tự phát hiện hồ sơ trùng, phê duyệt ngoài hệ thống.
- Microservices, event bus, queue hoặc scheduler khi chưa có use case.

## 4. Decision gates

| Gate | Quyết định cần có | Ảnh hưởng | Owner | Deadline | Trạng thái |
|---|---|---|---|---|---|
| DG-001 | Access contract cho Public Submission và Public Lookup | Public Lookup chỉ dùng `student_code`, không có xác minh bổ sung hoặc public detail/history route | Product Owner | 2026-07-30 | **Approved — 2026-07-30** |
| DG-002 | Thuật toán/tham số mật khẩu, session lifetime, login protection và reset password | Argon2id, tối thiểu 8 ký tự, không composition rule; chặn mật khẩu phổ biến/đã lộ khi khả dụng; database session expire-on-close + idle 120 phút; không Redis/remember-me/hard-lock; lỗi login chung | Security Owner | 2026-07-30 | **Approved — amended 2026-07-30** |
| DG-003 | Cách bootstrap Admin đầu tiên | Artisan command tương tác; không credential mặc định; không nhận password qua argument | Technical Lead + System Owner | 2026-07-30 | **Approved — 2026-07-30** |
| DG-004 | Phiên bản MariaDB cho development, test/CI và production target | Chặn production migration rehearsal | Technical Lead + Database Owner | 2026-07-30 | **Approved — MariaDB 10.11** |
| DG-005 | NFR-001 đến NFR-007 | Chặn performance/availability/accessibility acceptance | Product Owner + Operations Owner | 2026-08-07 | Pending |
| DG-006 | Idempotency cho Public Submission | Token server-generated, session-bound, TTL 10 phút; retry cùng token/payload trả cùng hồ sơ | Product Owner + Technical Lead | 2026-07-30 | **Approved — 2026-07-30** |

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
  → P8 Public modules
  → P9 System verification and deployment readiness
```

P4–P8 có thể được lập trình theo dependency sau khi P0 đạt.

## 7. Phase P0 — Decision và environment gates

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P0-01 | Chốt runtime baseline Laravel 13.x, PHP 8.4, Composer, Node.js/npm và kiểm tra extension bắt buộc | Không | **Completed — 2026-07-30** | Herd PHP 8.4.23 và extension đạt; Composer 2.10.2 qua User PATH; baseline Node 24.16.0/npm 11.13.0 được ghi tại `.nvmrc` và `docs/evidence/P0-01.md`; chưa yêu cầu `package.json` trước scaffold |
| P0-02 | Chuẩn bị MariaDB 10.11 test riêng và xác minh capability UTC/strict mode | P0-01 | **Completed — 2026-07-30** | MariaDB 10.11.15 chạy local-only; database dev/test tách biệt; live session áp dụng được `time_zone = '+00:00'` và `STRICT_TRANS_TABLES`; evidence tại `docs/evidence/P0-02.md`; chưa kết luận Laravel connection đã áp dụng |
| P0-03 | Tạo schema-only/sanitized baseline từ private SQL và import vào MariaDB test sạch | P0-02 | **Completed — 2026-07-30** | Baseline database-agnostic không chứa data statement; import sạch vào MariaDB 10.11 test; metadata và enforcement table/index/FK/CHECK/trigger đạt; evidence tại `docs/evidence/P0-03.md` |
| P0-04 | Thiết lập policy private import ngoài Git/CI | P0-03 | **Completed — 2026-07-30** | Private source nằm ngoài repository qua absolute `PRIVATE_IMPORT_PATH`; exact SQL allowlist và guard chống commit đạt; CI chỉ dùng sanitized baseline/fake fixtures và P1-06 phải chạy guard; evidence tại `docs/evidence/P0-04.md` |
| P0-05 | Theo dõi decision log DG-001 đến DG-006 | Owner của từng gate | In progress | DG-001/DG-002/DG-003/DG-004/DG-006 approved; chỉ DG-005 còn pending trước P9/production |
| P0-06 | Chốt DG-001 về access contract public | Product Owner | **Completed — 2026-07-30** | Lookup chỉ dùng `student_code`; không có xác minh bổ sung hoặc public detail/history route |

Exit criteria P0 cho phần nội bộ: P0-01 đến P0-04 đều đạt, sanitized baseline không chứa dữ liệu thật, MariaDB 10.11 server/test environment xác nhận có khả năng dùng UTC và `STRICT_TRANS_TABLES`. Việc Laravel connection thực sự áp dụng hai cấu hình thuộc P1. Không bắt đầu bất kỳ task P1 nào trước khi toàn bộ exit criteria này đạt.

## 8. Phase P1 — Laravel foundation

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P1-01 | Tạo Laravel 13.x application tại repository root và áp dụng runtime baseline P0-01 vào project metadata | P0-01, P0-02, P0-03, P0-04 | **Completed — 2026-07-30** | `package.json` dùng `engines` cho Node 24.16.0/npm 11.13.0 và `packageManager` là npm 11.13.0; Laravel 13.23.0 boot, PHPUnit và Vite production build đạt; guard local/CI đạt; evidence tại `docs/evidence/P1-01.md` |
| P1-02 | Cấu hình `.env.example`, MariaDB connection UTC/strict mode, locale, timezone và session | P1-01, P0-02 | Ready | DB có UTC/`STRICT_TRANS_TABLES`; `.env.example` đặt `SESSION_DRIVER=database`, `SESSION_EXPIRE_ON_CLOSE=true`, `SESSION_LIFETIME=120` |
| P1-03 | Tạo cấu trúc namespace theo Architecture | P1-01 | Ready | Có Enums, Exceptions, Requests, Services, Repositories, Policies, Support |
| P1-04 | Cấu hình code style/static analysis cho PHP và TypeScript/React | P1-08 | Ready after P1-08 | PHP format/static analysis và frontend format/lint/type-check chạy thành công |
| P1-05 | Tạo test bootstrap dùng MariaDB test và guard chống nhầm database | P1-02 | Ready | Test từ chối chạy nếu database không mang tên/phân loại test |
| P1-06 | Thiết lập và chạy xanh GitHub Actions workflow cho PR vào `develop`/`main` | P1-04, P1-05 | Ready | CI dùng đúng PHP 8.4, Node 24.16.0 và npm 11.13.0; lint, type-check, frontend test/build, static analysis, unit, feature, MariaDB integration và `migrate:fresh` chạy xanh; ghi nhận chính xác check names từ workflow run |
| P1-07 | Cấu hình GitHub Ruleset cho `main` và `develop` | P1-06 | Ready sau khi workflow xanh | Chỉ thêm đúng check names đã xuất hiện trong run xanh; required status checks áp cho PR vào cả hai branch |
| P1-08 | Cài React + TypeScript, Inertia Laravel/React adapter và cấu hình Vite | P1-01 | Ready after P1-01 | `app.tsx`, `app.blade.php`, Inertia middleware và sample page render được; production build không lỗi |

Critical path P1: `P1-01 → P1-08 → P1-04 → P1-06 → P1-07`; P1-06 đồng thời phụ thuộc P1-05. Exit criteria P1: application boot/config cache đạt trên PHP 8.4; React/Inertia page và Vite production build đạt bằng Node.js/npm đã pin; integration check chứng minh Laravel connection thực tế có `time_zone = '+00:00'` và SQL mode chứa `STRICT_TRANS_TABLES`; P1-06 workflow đã xanh trước, sau đó P1-07 Ruleset dùng đúng check names cho `develop`/`main`.

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
| P2-08 | Tạo migration cho `sessions` và `public_submission_idempotency` | P2-01 | Ready | Database session hoạt động; idempotency có token unique, session ID, payload hash, document reference và expiry |

Integration tests bắt buộc:

- `invalid_reason` đúng invariant hai chiều ở cả current/history.
- `completed_at` có giá trị khi và chỉ khi status là `completed`.
- `document_code` unique và trigger từ chối update.
- FK từ chối xóa Student có hồ sơ và từ chối mất liên kết history.
- Độ dài `note`/`invalid_reason` khớp schema.
- MariaDB session integration test fail nếu thiếu `STRICT_TRANS_TABLES`.
- `sessions` dùng MariaDB database driver; `public_submission_idempotency` bảo vệ token bằng unique constraint.
- CI scan/test xác nhận không có dữ liệu thật hoặc private import file trong repository/artifact.

Exit criteria P2: `migrate:fresh` và rollback/rebuild trên MariaDB test thành công; metadata đối chiếu với sanitized baseline; factories không chứa dữ liệu thật; private import command/path không được CI tải hoặc chạy.

## 10. Phase P3 — Shared application infrastructure

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P3-01 | Tạo `Clock` contract và `SystemClock` | P1-03 | Ready | Test đóng băng thời gian không cần test-only hook trong production class |
| P3-02 | Tạo exception taxonomy và mapping trong `bootstrap/app.php` | P1-03 | Ready | Inertia/Blade/JSON trả đúng status/behavior đã chốt |
| P3-03 | Tạo `RequestTrace` middleware và `ApiResponse` | P3-02 | Ready | Response/log có cùng trace ID, không lộ stack trace |
| P3-04 | Tạo React layouts/components và Blade components dùng chung cho flash/errors | P1-08, P3-02 | Ready | Inertia shared props và Blade PRG hiển thị `success`, `warning`, `error` nhất quán |
| P3-05 | Đăng ký Repository bindings trong `AppServiceProvider` | P2-05 | Ready | Container resolve đúng interface |
| P3-06 | Tạo policy/role infrastructure và route groups nội bộ | P2-04 | Ready | Route nội bộ mặc định yêu cầu auth và server-side role check |
| P3-07 | Tạo Security Headers middleware | P1-03 | Ready | Feature test kiểm tra CSP/frame policy, content-type protection, referrer policy và HSTS chỉ trên production HTTPS |
| P3-08 | Tạo application liveness và database readiness health checks | P1-02, P2-01 | Ready | DB check dùng query nhẹ; status tách biệt; response không lộ DSN, credential hoặc SQL error |

Exit criteria P3: exception/response/trace/authorization foundation, security headers và hai loại health check có feature/integration test trước khi module dùng.

## 11. Phase P4 — Identity & Access

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P4-01 | Ánh xạ `User` Authenticatable với `password_hash` và `is_active` | P2-03 | Ready | Hash login đúng; inactive user bị từ chối |
| P4-02 | Tạo Login Form Request, Auth Service, Controller và React/Inertia page | P2-08, P3-02, P3-04, P3-06, P4-01 | Ready | Login regenerate database session; sai credential không tạo session, không hard-lock/đổi `users.is_active`, và luôn trả lỗi chung |
| P4-03 | Tạo logout flow | P4-02 | Ready | Invalidate session và regenerate CSRF token |
| P4-04 | Áp role middleware/Gates/Policies cho Staff, Thư ký, Admin | P4-02 | Ready | Direct URL/action trái quyền trả đúng 403/redirect |
| P4-05 | Triển khai chính sách password/session/login protection/reset theo DG-002 | P2-08, P4-02, DG-002 | Ready | Argon2id; tối thiểu 8 ký tự; không composition rule; chặn password phổ biến/đã lộ khi khả dụng; expire-on-close + idle 120 phút; không Redis/remember-me/hard-lock; reset tạm không ép đổi |
| P4-06 | Tạo quy trình bootstrap Admin theo DG-003 | P4-01, DG-003 | Ready | Artisan command tương tác; không credential mặc định/password argument; không commit hoặc log password |

Exit criteria P4: P4-01 đến P4-06 đều đạt, gồm test Argon2id, độ dài tối thiểu 8, không composition rule, kiểm tra mật khẩu phổ biến/đã lộ khi khả dụng, database session expire-on-close và idle timeout 120 phút, không remember-me, login failure chung không đổi `users.is_active`, reset mật khẩu tạm và Artisan bootstrap Admin.

## 12. Phase P5 — Administration và directories

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P5-01 | Quản lý User qua Controller/Form Request/Service/Repository | P4-04 | Ready | Create/read/update/activate/deactivate; không có destroy route |
| P5-02 | Quản lý Document Type | P4-04 | Ready | Create/read/update/activate/deactivate; không hard delete |
| P5-03 | Quản lý Student | P4-04 | Ready | CRUD; chỉ xóa khi chưa liên kết hồ sơ |
| P5-04 | Tạo search/filter/pagination cho ba màn hình | P5-01, P5-02, P5-03 | Ready | Query qua Repository, giữ filter khi phân trang |
| P5-05 | Tạo React/Inertia pages quản trị User, Document Type và Student | P3-04, P5-04 | Ready | URL giữ filter/page; form hiển thị validation/flash; loading/empty/error states có component tests |

Integration/feature tests bắt buộc:

- Không tồn tại endpoint hard delete User/Document Type.
- Khóa User không làm mất history; document type inactive không mất hồ sơ.
- Student có liên kết bị từ chối xóa; Student chưa liên kết xóa được.
- Role ngoài Admin không gọi trực tiếp được action quản trị.
- Không khóa/đổi role Thư ký còn phụ trách hồ sơ mở nếu chưa tái phân công toàn bộ sang Thư ký đang hoạt động khác.
- Tái phân công và khóa/đổi role commit hoặc rollback nguyên tử.

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
| P6-09 | Xây đầy đủ React/Inertia UI cho Staff | P3-04, P6-07 | Ready | List/filter/detail/history/receive UI; URL filter, responsive states, component và feature tests |
| P6-10 | Xây đầy đủ React/Inertia UI cho Secretary | P3-04, P6-07, P6-08 | Ready | List/filter/detail/history/receive/transition/note/reason UI; receive submit cùng shared endpoint với Staff |

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
- Test mọi Secretary đang hoạt động có thể xử lý hồ sơ được phân công cho Secretary khác; assignment không tạo authorization độc quyền.
- Test Secretary khác xử lý không tự ghi đè `assigned_secretary_user_id`.
- Feature/component test đầy đủ React/Inertia UI Staff/Secretary: route, props, URL filter, empty/error state, validation, flash, authorization và submit transition.

Exit criteria P6: P6-01 và P6-02 có thể hoàn thành song song; P6-03 đến P6-10 đạt; AC-FR-006, AC-FR-007, AC-FR-008 và React/Inertia UI Staff/Secretary đều qua frontend/feature/integration tests trên MariaDB.

## 14. Phase P7 — Reporting

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P7-01 | Tạo Report Repository query đủ bảy trạng thái và theo loại | P2-05, P4-04 | Ready | Kết quả luôn có đủ bảy key, kể cả count bằng 0 |
| P7-02 | Tạo Report Service chuyển biên local date/month sang UTC | P3-01, P7-01 | Ready | Dùng khoảng nửa mở trên `submitted_at` |
| P7-03 | Tạo Admin Report Controller/Form Request và React/Inertia page | P3-04, P7-02 | Ready | Chỉ Admin truy cập; filter ngày/tháng hợp lệ; dữ liệu biểu đồ nặng chỉ tải khi cần |
| P7-04 | Tối ưu query/index theo dữ liệu đo | P7-03, DG-005 | Pending NFR | Explain plan và response time đạt ngưỡng đã duyệt |

Integration tests bắt buộc:

- Trả đủ `waiting_for_receipt`, `received`, `processing`, `needs_supplement`, `completed`, `invalid`, `cancelled`.
- Record đúng tại biên đầu ngày, sát biên cuối và đầu ngày kế tiếp theo `Asia/Ho_Chi_Minh`.
- Tháng dùng `[monthStartUtc, nextMonthStartUtc)` và không double-count.
- Role ngoài Admin bị từ chối.

Exit criteria P7 chức năng: AC-FR-011 đạt. Performance acceptance chờ DG-005.

## 15. Phase P8 — Public Submission và Public Lookup

> DG-001 và DG-006 đã được phê duyệt. Public Lookup chỉ dùng `student_code`; Public Submission dùng idempotency token session-bound TTL 10 phút. P8 triển khai theo dependency bên dưới.

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P8-00 | Chốt DG-001 và DG-006 | Product Owner + Technical Lead | **Completed — 2026-07-30** | Access contract và idempotency đã được phê duyệt |
| P8-01 | Đồng bộ Requirements/Architecture theo hai quyết định | P8-00 | **Completed — 2026-07-30** | Requirement, acceptance criteria, route và idempotency contract đã đồng bộ |
| P8-02 | Triển khai `DocumentCodeGenerator` thuần với injected Clock/random source | P8-01, P3-01 | Ready | Chỉ trả candidate code đúng alphabet/ngày; không Repository/insert/catch/retry |
| P8-03 | Triển khai Student verification và active document types | P8-01, P2-05, P2-06 | Ready | Chỉ phụ thuộc Repository; verify `student_code`; dùng dữ liệu giả/sanitized cần thiết |
| P8-04 | Triển khai `StudentDocumentService` create/insert/collision retry | P8-02, P2-05 | Ready | Không pre-check; chỉ retry đúng unique constraint tối đa 5; lỗi khác ném lại |
| P8-05 | Triển khai Public Submission Form Request/Controller/Blade | P8-03, P8-04, P8-08 | Ready | Tạo waiting document, không tạo history; retry cùng token không tạo hồ sơ thứ hai |
| P8-06 | Triển khai Public Lookup Form Request/Controller/Blade | P8-01, P2-05 | Ready | Chỉ nhận `student_code`; output allowlist FR-002/FR-009; không có public detail/history route |
| P8-07 | Áp kiểm soát nền tảng cho endpoint public | P8-03, P8-05, P8-06 | Ready | HTTPS, validation, escaping, CSRF cho mutation và rate limit chung hoạt động; không thêm xác minh danh tính |
| P8-08 | Triển khai idempotency token cho Public Submission | P2-08, P3-01, P8-01 | Ready | Bảng MariaDB riêng; token unique, session ID, payload hash, document reference, expiry; transaction + row lock; không memory/file/Redis |

Tests bắt buộc cho P8:

- Mã hồ sơ đúng regex, alphabet và ngày `Asia/Ho_Chi_Minh`.
- Unit test generator chứng minh không có persistence/retry side effect.
- Integration test Service chứng minh collision chỉ retry khi lỗi đúng `uq_student_documents_document_code`; lỗi khác được ném lại; lần thứ 5 thất bại tạo exception chuẩn.
- Kết quả submit hiển thị `document_code` vừa tạo nhưng Public Lookup không nhận mã này làm input và không có public detail route theo mã hồ sơ/ID tuần tự.
- Public Submission tạo `student_documents` ở `waiting_for_receipt` nhưng không tạo history; Staff hoặc Secretary receive mới tạo history đầu tiên.
- Submission cùng loại vẫn tạo hồ sơ mới theo BR-003.
- Hai request đồng thời hoặc tuần tự có cùng token và payload chỉ tạo một hồ sơ và trả cùng kết quả.
- Test xác nhận unique constraint, transaction và `lockForUpdate()` trên `public_submission_idempotency`; nguồn quyết định không nằm trong memory, file cache, Redis hoặc session payload.
- Cùng token nhưng payload khác, token hết hạn hoặc token không hợp lệ bị từ chối mà không tạo hồ sơ.
- Form/token mới cho phép chủ động tạo hồ sơ mới cùng loại theo BR-003.
- Lookup chỉ cần `student_code`, trả toàn bộ danh sách đúng mã với đúng trường FR-009; không trả lịch sử hoặc dữ liệu ngoài allowlist.
- Request có `document_code` nhưng không có `student_code` không được xem là lookup hợp lệ; hệ thống không cung cấp public detail/history route.
- HTTPS, validation và rate limit chung hoạt động; Public Lookup không yêu cầu OTP, CAPTCHA, đăng nhập, `document_code` hoặc yếu tố xác minh bổ sung.

Exit criteria P8: P8-01 đến P8-08 đạt, AC-FR-001 đến AC-FR-004 và AC-SEC-001 đạt; lookup chỉ dùng `student_code`, output đúng allowlist, idempotency đúng BR-013 và submission không có history trước lần receive.

## 16. Phase P9 — System verification và deployment readiness

> Không bắt đầu task P9 nào trước khi DG-005 được phê duyệt. DG-005 không chặn triển khai chức năng P0–P8.

| ID | Task | Dependency | Trạng thái | Output/verification |
|---|---|---|---|---|
| P9-01 | Chạy full frontend/unit/feature/integration suite qua local và GitHub Actions | P1-07, P4–P8 theo phạm vi release | Ready khi phases hoàn tất | Type-check, frontend test/build và backend suites đều xanh; không có required check fail/flaky chưa xử lý |
| P9-02 | Security review auth, authorization, CSRF, security headers, public surface, log redaction | P3-07, P4, P6, P8 nếu public release | Pending dependencies | AC-SEC-001, header tests và decision controls đạt |
| P9-03 | Migration rehearsal trên bản sao dữ liệu thật trong môi trường private | P0-04, DG-004, P2 | Pending | Không chạy trên CI, không upload cache/artifact/log chứa dữ liệu; access có kiểm soát; backup/restore/rollback đạt; có retention deadline và bằng chứng xóa bản sao sau diễn tập |
| P9-04 | Kiểm thử NFR | DG-005, functional complete | **Blocked by DG-005** | Mỗi NFR có điều kiện đo và ngưỡng đạt |
| P9-05 | Build/deploy rehearsal | P3-08, P9-01, P9-02, P9-03 | Pending | Vite production assets đi cùng đúng Laravel release; config/route/view cache, liveness, DB readiness và rollback hoạt động |
| P9-06 | UAT theo acceptance criteria | Phạm vi release hoàn tất | Pending | Biên bản nghiệm thu và known issues |

Chỉ gọi bản release là public MVP hoàn chỉnh khi toàn bộ P8 đã hoàn tất. DG-005 không chặn triển khai chức năng P0–P8 nhưng phải được chốt trước khi bắt đầu acceptance P9 và production sign-off.

Exit criteria P9: CI required checks xanh trên `develop`/`main`; private migration rehearsal đạt và bản sao dữ liệu được xóa theo policy; security headers và health checks đạt; UAT/NFR được ghi rõ `Đạt` hoặc `Chưa thể kiểm thử` theo decision gates.

## 17. Test strategy và quality gates

### 17.1 Test pyramid áp dụng

- Unit: pure state rules, generator, Clock conversion và exception mapping nhỏ.
- Frontend: TypeScript type-check, component behavior và các page state của React.
- Feature: route, Form Request, Inertia props/redirect/flash, Blade public behavior, authentication và authorization.
- Integration ưu tiên: Repository, MariaDB constraint, transaction, locking, report query và deletion behavior.
- End-to-end: các workflow chính sau khi từng module qua feature/integration tests.

### 17.2 Quality gate cho mỗi pull request

- Format/lint/static analysis đạt.
- TypeScript strict type-check, frontend component tests và Vite production build đạt.
- Test mới bao phủ hành vi user-facing hoặc invariant thay đổi.
- Migration được test trên MariaDB fresh database.
- Không có Controller gọi Model/DB hoặc Service gọi Eloquent/Query Builder.
- Không log credential, cookie, full note hoặc full invalid reason.
- Route mới có Form Request và authorization test.
- Public route phải tuân DG-001/DG-006: lookup chỉ nhận `student_code`, không có xác minh bổ sung hoặc public detail/history route; submission dùng idempotency token và output đúng allowlist.
- GitHub Actions trên PR vào `develop`/`main` phải chạy lint, static analysis, unit, feature, MariaDB integration và `migrate:fresh` bằng PHP 8.4.
- Chỉ cấu hình Ruleset sau khi workflow đã có một run xanh; required status checks phải dùng đúng check names ghi nhận từ run đó.
- CI không tải, cache hoặc artifact private import/dữ liệu 8.145 Sinh viên.

## 18. Traceability triển khai

| Nhóm requirement | Phase chính | Verification |
|---|---|---|
| FR-001 đến FR-009, BR-013, SEC-006 | P8 | DG-001/DG-006 approved; lookup `student_code`-only; idempotency tests; AC-FR-001 đến AC-FR-004 |
| FR-010, FR-011, SEC-001 đến SEC-005 | P3-07, P4 | Auth/role/header feature tests, AC-FR-005, AC-SEC-001 |
| FR-012 đến FR-022, BR-003 đến BR-009 | P6 | State matrix, Staff/Secretary React/Inertia UI, transaction/concurrency tests |
| FR-023 đến FR-026, BR-011 | P5 | Admin React/Inertia UI và CRUD/activation/deletion tests |
| FR-027, FR-028, BR-012 | P7 | Seven-status và UTC boundary tests |
| BR-010 | P8-02, P8-04, P8-05 | Pure generator, Service collision retry, unique/immutable/redirect tests |
| DR-001 đến DR-008 | P0-03, P2 | Sanitized metadata baseline, domain/support migrations và MariaDB integration tests |
| Runtime/CI/operations | P0-01, P1-02, P1-06, P1-07, P3-08 | PHP 8.4, strict UTC DB session, required CI checks và health tests |
| NFR-001 đến NFR-007 | P9-04 | Chờ DG-005 |
| UI-001 đến UI-003 | P1-08, P3-04, P4–P7 | React/Inertia internal pages, Blade public/error pages, frontend build/tests và server-side authorization tests |

## 19. Rủi ro và kiểm soát

| Rủi ro | Tín hiệu sớm | Kiểm soát |
|---|---|---|
| Public response/idempotency sai contract | Yêu cầu thêm xác minh, có public detail/history route, trả thêm trường hoặc retry tạo hồ sơ thứ hai | Lookup `student_code`-only; allowlist response; token consume nguyên tử và concurrency tests |
| Session/idempotency phụ thuộc một process | Dùng file, memory cache hoặc Redis ngoài phạm vi MVP | MariaDB database session; bảng idempotency riêng với transaction, row lock và unique constraint; integration test request đồng thời |
| Migration khác baseline | Metadata/index/constraint name lệch | P0 schema-only/sanitized baseline, P2 metadata comparison, MariaDB integration tests |
| Dữ liệu thật lọt vào Git/CI | Có row/mã Sinh viên thật trong fixture/artifact | External private path, ignore/scan gate, fake factories và sanitized baseline |
| Bản sao rehearsal tồn tại quá hạn | Không có owner/deletion evidence sau P9-03 | Private isolated environment, retention deadline, owner và verified deletion record |
| Lost update/history sai | Read-then-write ngoài transaction | Service-owned transaction và `lockForUpdate()` test đồng thời |
| Timezone lệch báo cáo/mã | Dùng `now()` trực tiếp hoặc `BETWEEN` cuối ngày | Inject Clock, DB UTC, half-open UTC boundaries |
| Repository thành CRUD abstraction rỗng | Xuất hiện `BaseRepository` | Review theo use case contract; quality gate cấm base generic |
| Quyền xóa bị mở rộng | Có destroy route User/Document Type | Route/feature test xác nhận 404/405 và Service policy |
| NFR bị coi là đạt khi chưa có ngưỡng | Báo cáo test không ghi điều kiện đo | DG-005 và AC-NFR-001 là production gate |
| Client chứa business rule hoặc quyền quyết định | Transition/role check chỉ xuất hiện trong React | React chỉ biểu diễn UI; Form Request/Policy/Service và server-side tests là nguồn quyết định |
| Bundle React tăng không kiểm soát | Mọi page import chart/editor ngay entry point | Import trực tiếp, lazy-load thư viện nặng theo page và kiểm tra production build trong CI |

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
2. Chỉ sau khi P0 exit đạt: P1-01 → P1-08 → P1-04 → P1-06 → P1-07; P1-06 đồng thời chờ P1-05.
3. P2-01 → P2-08 và P3-01 → P3-08 để khóa schema, persistence, database session/idempotency, security headers và health checks.
4. P4, P5, P6, P7 theo dependency; P6-01 và P6-02 chạy song song.
5. Triển khai P8 theo dependency với DG-001/DG-006 đã completed.
6. Chốt DG-005, sau đó thực hiện P9 theo phạm vi release đã được phê duyệt.

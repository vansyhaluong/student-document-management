# REQUIREMENTS — Hệ thống quản lý tiếp nhận hồ sơ sinh viên

> Trạng thái: Approved for Conditional Implementation — P0–P8; production gates pending

## Project Overview

Hệ thống quản lý tiếp nhận hồ sơ sinh viên hỗ trợ sinh viên khoa Công nghệ thông tin (CNTT) khai báo hồ sơ giấy đã nộp trực tiếp, nhận mã hồ sơ và tra cứu trạng thái xử lý trên website.

Hệ thống không tiếp nhận tệp hồ sơ điện tử. Hồ sơ giấy được sinh viên giao trực tiếp cho Thư ký. Thư ký kiểm tra, xử lý và chuyển hồ sơ giấy đến phòng ban bên ngoài hệ thống để phê duyệt cuối. Sau khi nhận kết quả phê duyệt, Thư ký cập nhật kết quả thủ công trên hệ thống.

Sinh viên không có tài khoản và không phải đăng nhập. Staff, Thư ký và Admin sử dụng tài khoản nội bộ.

## Goals

- Cho phép sinh viên xác nhận thông tin cơ bản bằng mã sinh viên trước khi khai báo hồ sơ giấy đã nộp.
- Ghi nhận mỗi yêu cầu hồ sơ với mã hồ sơ duy nhất và trạng thái ban đầu xác định.
- Cho phép sinh viên tra cứu hồ sơ và trạng thái hiện tại mà không cần tài khoản.
- Cho phép Staff khoa CNTT xem, tìm kiếm, lọc, xem lịch sử và xác nhận tiếp nhận hồ sơ.
- Cho phép Thư ký khoa CNTT quản lý quá trình xử lý hồ sơ theo luồng trạng thái quy định.
- Lưu vết đầy đủ mỗi lần trạng thái hồ sơ thay đổi.
- Cho phép Admin quản lý dữ liệu vận hành của website và xem báo cáo thống kê.

## Scope

Hệ thống là một website phục vụ riêng cho khoa CNTT. Hệ thống quản lý thông tin khai báo và trạng thái xử lý của hồ sơ giấy; việc giao nhận hồ sơ giấy và phê duyệt cuối tại phòng ban khác diễn ra ngoài hệ thống.

### In Scope

- Kiểm tra mã sinh viên thuộc danh sách sinh viên của hệ thống.
- Hiển thị thông tin cơ bản của sinh viên.
- Gửi yêu cầu ghi nhận hồ sơ giấy đã nộp.
- Sinh mã hồ sơ duy nhất.
- Cho phép Sinh viên gửi nhiều hồ sơ cùng loại; hồ sơ trùng được Thư ký xử lý.
- Tra cứu danh sách hồ sơ và trạng thái hiện tại.

- Đăng nhập cho Staff, Thư ký và Admin.
- Staff xem, tìm kiếm, lọc, xem chi tiết, xem lịch sử và tiếp nhận hồ sơ.
- Thư ký xem, tìm kiếm, lọc, xem chi tiết, xem lịch sử và xử lý trạng thái hồ sơ.
- Thư ký cập nhật thủ công kết quả phê duyệt nhận từ phòng ban khác.
- Lưu lịch sử thay đổi trạng thái.
- Admin quản lý tài khoản Staff/Thư ký, loại hồ sơ, danh sách sinh viên và phân quyền.

- Admin xem báo cáo và thống kê.

### Out of Scope

- Tải lên, lưu trữ hoặc xử lý tệp hồ sơ điện tử.
- Nhập đầy đủ thông tin cá nhân của sinh viên khi gửi yêu cầu hồ sơ.
- Tài khoản và chức năng đăng nhập dành cho sinh viên.
- Chức năng xem lịch sử xử lý dành cho Sinh viên.
- Chức năng quản lý nhiều khoa hoặc danh mục khoa.
- Tài khoản hoặc màn hình dành cho phòng ban phê duyệt cuối.
- Tự động đồng bộ kết quả từ phòng ban phê duyệt.
- Thực hiện quy trình phê duyệt cuối trên hệ thống.
- Thiết kế cơ sở dữ liệu vật lý hoặc chi tiết triển khai.

## UI Delivery Constraints

#### UI-001 — React cho khu vực nội bộ

Các màn hình đăng nhập và chức năng nội bộ của Staff, Thư ký, Admin phải dùng React + TypeScript qua Inertia.js trong cùng Laravel application. Không tách frontend repository hoặc xây token-based API chỉ để phục vụ các màn hình này trong MVP.

#### UI-002 — Blade cho khu vực công khai và trang lỗi

Public Submission, Public Lookup, trang lỗi và HTML shell của Inertia dùng Blade. Việc phân chia công nghệ giao diện không được thay đổi workflow, quyền hoặc dữ liệu hiển thị đã quy định.

#### UI-003 — Máy chủ là nguồn quyết định nghiệp vụ

React có thể ẩn hoặc vô hiệu hóa thao tác để cải thiện trải nghiệm, nhưng mọi validation, authorization, transition và transaction bắt buộc vẫn phải được kiểm tra phía Laravel. Inertia dùng web route, session authentication và CSRF hiện có.

## User Roles

| Vai trò | Mô tả | Quyền chính |
|---|---|---|
| Sinh viên | Người dùng không có tài khoản, sử dụng mã sinh viên để khai báo và tra cứu | Kiểm tra mã sinh viên, gửi yêu cầu và tra cứu hồ sơ |
| Staff | Nhân sự khoa CNTT có tài khoản nội bộ | Xem, tìm kiếm, lọc, xem chi tiết, xem lịch sử và chuyển hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận` |
| Thư ký | Nhân sự khoa CNTT trực tiếp nhận hồ sơ giấy và quản lý quá trình xử lý | Toàn bộ quyền tra cứu hồ sơ; tiếp nhận, cập nhật trạng thái, ghi chú, hủy hồ sơ và cập nhật kết quả phê duyệt |
| Admin | Người quản trị toàn hệ thống | Quản lý tài khoản, sinh viên, loại hồ sơ, phân quyền và xem báo cáo |

## Functional Requirements

### Chức năng dành cho Sinh viên

#### FR-001 — Kiểm tra mã sinh viên

Hệ thống phải cho phép Sinh viên nhập mã sinh viên và kiểm tra mã đó có tồn tại trong danh sách sinh viên của hệ thống.

#### FR-002 — Hiển thị thông tin sinh viên

Khi mã sinh viên tồn tại, hệ thống phải hiển thị đúng mã sinh viên và họ tên.

#### FR-003 — Thông báo mã sinh viên không tồn tại

Khi mã sinh viên không tồn tại, hệ thống phải hiển thị thông báo lỗi, không cho phép gửi yêu cầu và không cho phép tra cứu hồ sơ.

#### FR-004 — Hiển thị loại hồ sơ được tiếp nhận

Hệ thống phải hiển thị các loại hồ sơ đang hoạt động do Admin cấu hình để Sinh viên chọn. Danh sách ví dụ trong SRS không phải danh sách cố định hoặc bắt buộc khi nghiệm thu.

#### FR-005 — Gửi yêu cầu hồ sơ

Hệ thống phải cho phép Sinh viên đã được xác nhận mã sinh viên chọn một loại hồ sơ và gửi yêu cầu ghi nhận hồ sơ giấy đã nộp. Form gửi phải chứa idempotency token do máy chủ sinh theo BR-013.

#### FR-006 — Khởi tạo yêu cầu hồ sơ

Khi yêu cầu hợp lệ được gửi, hệ thống phải lưu mã sinh viên, loại hồ sơ, thời gian gửi, trạng thái `Chờ tiếp nhận` và một mã hồ sơ duy nhất do hệ thống tự sinh theo BR-010. Việc khởi tạo này không tạo bản ghi lịch sử trạng thái.

#### FR-007 — Cho phép gửi nhiều hồ sơ cùng loại

Hệ thống phải tạo yêu cầu mới khi Sinh viên gửi hồ sơ cùng loại với hồ sơ đã có, không phụ thuộc trạng thái của các hồ sơ trước đó.

#### FR-008 — Tra cứu danh sách hồ sơ

Hệ thống phải cho phép Sinh viên nhập duy nhất mã sinh viên để xem toàn bộ danh sách hồ sơ gắn với mã đó mà không cần đăng nhập. Không yêu cầu mã hồ sơ, mật khẩu, OTP, CAPTCHA hoặc yếu tố xác minh thứ hai.

#### FR-009 — Hiển thị thông tin tra cứu hồ sơ

Đối với mỗi hồ sơ trong danh sách kết quả, hệ thống phải hiển thị mã hồ sơ, loại hồ sơ, ngày gửi, trạng thái hiện tại, lý do `Không hợp lệ` nếu có, ghi chú hiện tại của Thư ký nếu có và ngày cập nhật gần nhất. Sinh viên không có chức năng xem lịch sử xử lý hoặc trang public xem chi tiết riêng một hồ sơ; `document_code` chỉ là dữ liệu hiển thị, không phải input tra cứu.

### Chức năng dùng chung cho người dùng nội bộ

#### FR-010 — Đăng nhập

Hệ thống phải cung cấp chức năng đăng nhập bằng tài khoản nội bộ cho Staff, Thư ký và Admin.

#### FR-011 — Kiểm soát truy cập theo vai trò

Sau khi đăng nhập, hệ thống phải chỉ cho phép tài khoản sử dụng các chức năng được cấp cho vai trò hiện tại.

### Chức năng dành cho Staff

#### FR-012 — Xem danh sách hồ sơ

Staff phải xem được danh sách hồ sơ của khoa CNTT.

#### FR-013 — Tìm kiếm và lọc hồ sơ

Staff phải có thể tìm kiếm hoặc lọc hồ sơ theo mã sinh viên, họ tên sinh viên, loại hồ sơ, trạng thái và ngày gửi.

#### FR-014 — Xem chi tiết và lịch sử hồ sơ

Staff phải xem được chi tiết hồ sơ và toàn bộ lịch sử thay đổi trạng thái của hồ sơ thuộc khoa CNTT.

#### FR-015 — Staff xác nhận tiếp nhận hồ sơ

Staff phải có thể chuyển hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận`. Staff không được thực hiện các chuyển trạng thái khác.

### Chức năng dành cho Thư ký

#### FR-016 — Xem và tra cứu hồ sơ

Thư ký phải xem được danh sách, chi tiết và lịch sử hồ sơ của khoa CNTT; đồng thời phải có thể tìm kiếm hoặc lọc theo mã sinh viên, họ tên sinh viên, loại hồ sơ, trạng thái và ngày gửi.

#### FR-017 — Thư ký xác nhận tiếp nhận hồ sơ

Thư ký phải có thể chuyển hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận`.

#### FR-018 — Cập nhật trạng thái xử lý

Mọi Thư ký có tài khoản đang hoạt động phải có thể cập nhật trạng thái hồ sơ theo các bước chuyển hợp lệ được định nghĩa tại BR-005, kể cả khi hồ sơ đang được phân công cho Thư ký khác. `assigned_secretary_user_id` biểu thị người chịu trách nhiệm chính, không tạo authorization độc quyền. Khi Thư ký bắt đầu xử lý bằng bước `Đã tiếp nhận` → `Đang xử lý`, hệ thống gán Thư ký đó làm người đang được phân công phụ trách nếu hồ sơ chưa có người phụ trách; Thư ký khác xử lý hồ sơ không được tự động ghi đè phân công. Staff không được thay đổi phân công này. Khi chuyển hồ sơ sang `Không hợp lệ`, Thư ký phải kiểm tra thủ công và nhập `invalid_reason` dưới dạng văn bản tự do, tối đa 200 ký tự, theo BR-009. Khi phát hiện hồ sơ trùng, Thư ký tự chọn hồ sơ cần xử lý theo BR-003.

#### FR-019 — Nhập ghi chú xử lý

Thư ký phải có thể nhập ghi chú bổ sung tối đa 500 ký tự khi cập nhật hồ sơ. `student_documents.note` là ghi chú của trạng thái hiện tại; nếu lần chuyển mới không có ghi chú thì ghi chú hiện tại phải được đặt về `NULL`. Ghi chú không phụ thuộc vào nội dung của `invalid_reason`. `document_status_history.note` là snapshot bất biến của ghi chú tại từng lần chuyển. Nếu trạng thái hiện tại có ghi chú, hệ thống phải hiển thị ghi chú đó cho Sinh viên khi tra cứu hồ sơ.

#### FR-020 — Cập nhật kết quả phê duyệt ngoài hệ thống

Trong thời gian hồ sơ giấy được phòng ban khác phê duyệt, hồ sơ phải giữ trạng thái `Đang xử lý`. Sau khi nhận kết quả, Thư ký phải cập nhật kết quả thủ công trên hệ thống.

#### FR-021 — Thư ký hủy hồ sơ

Thư ký phải có thể chuyển hồ sơ ở bất kỳ trạng thái chưa kết thúc nào sang `Đã hủy`.

#### FR-022 — Lưu lịch sử trạng thái

Sau mỗi lần thay đổi trạng thái, hệ thống phải tự động lưu trạng thái mới, người cập nhật, thời gian cập nhật, nội dung ghi chú nếu có và `invalid_reason` dạng văn bản tự do nếu trạng thái mới là `Không hợp lệ`. Bản ghi lịch sử đầu tiên được tạo khi Staff hoặc Thư ký chuyển hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận`; Public Submission không tạo lịch sử.

### Chức năng dành cho Admin

#### FR-023 — Quản lý tài khoản nội bộ

Admin phải có thể thêm, xem, sửa, khóa, mở khóa và đặt lại mật khẩu cho tài khoản Staff/Thư ký. Khi đặt lại, Admin cấp một mật khẩu tạm đáp ứng chính sách mật khẩu; người dùng không bắt buộc đổi mật khẩu ở lần đăng nhập tiếp theo. Hệ thống không cung cấp chức năng xóa vật lý tài khoản; tài khoản không còn sử dụng phải được khóa để bảo toàn liên kết và lịch sử. Admin không được khóa một Thư ký đang là người chịu trách nhiệm chính của bất kỳ hồ sơ chưa kết thúc nào, trừ khi toàn bộ hồ sơ mở đó được tái phân công cho Thư ký đang hoạt động khác trong cùng thao tác nghiệp vụ.

#### FR-024 — Phân quyền tài khoản

Admin phải có thể gán vai trò Staff hoặc Thư ký cho tài khoản nội bộ. Admin không được đổi vai trò Thư ký sang vai trò khác khi tài khoản đó còn là người chịu trách nhiệm chính của hồ sơ chưa kết thúc, trừ khi toàn bộ hồ sơ mở được tái phân công cho Thư ký đang hoạt động khác trong cùng thao tác nghiệp vụ.

#### FR-025 — Quản lý loại hồ sơ

Admin phải có thể thêm, xem, sửa, kích hoạt và ngừng sử dụng loại hồ sơ. Hệ thống không cung cấp chức năng xóa vật lý loại hồ sơ; loại không còn sử dụng phải được ngừng kích hoạt.

#### FR-026 — Quản lý danh sách sinh viên

Admin phải có thể thêm, xem, sửa và xóa Sinh viên. Sinh viên đã được liên kết với hồ sơ không được xóa.

#### FR-027 — Xem thống kê đầy đủ bảy trạng thái

Admin phải xem được số hồ sơ hiện có theo đủ bảy trạng thái: `Chờ tiếp nhận`, `Đã tiếp nhận`, `Đang xử lý`, `Cần bổ sung`, `Đã xử lý xong`, `Không hợp lệ`, `Đã hủy`; đồng thời xem được số hồ sơ theo từng loại.

#### FR-028 — Xem thống kê theo thời gian

Admin phải xem được số hồ sơ theo ngày và tháng.

## Business Rules

#### BR-001 — Mã sinh viên phải tồn tại

Sinh viên được gửi yêu cầu và tra cứu hồ sơ khi mã sinh viên tồn tại trong danh sách sinh viên của hệ thống. Hệ thống không tự xác định tình trạng còn học hay thôi học; Thư ký kiểm tra thủ công tình trạng học tập trong quá trình xử lý hồ sơ theo WF-003.

#### BR-002 — Loại hồ sơ phải được phép tiếp nhận

Sinh viên chỉ được chọn loại hồ sơ đang hoạt động do Admin cấu hình cho website của khoa CNTT.

#### BR-003 — Xử lý hồ sơ gửi trùng

Sinh viên được gửi nhiều yêu cầu cùng loại, không phụ thuộc trạng thái của hồ sơ đã có. Hệ thống không tự xác định hồ sơ trùng. Thư ký kiểm tra thủ công và được chọn hồ sơ cụ thể cần xử lý là hồ sơ trùng; các hồ sơ còn lại không bị thay đổi. Đối với hồ sơ đã chọn, Thư ký phải nhập `invalid_reason` dạng văn bản tự do và chuyển hồ sơ từ `Đang xử lý` sang `Không hợp lệ`. Ghi chú bổ sung không bắt buộc.

#### BR-004 — Phạm vi hồ sơ nội bộ

Staff và Thư ký chỉ được xem và thao tác trên hồ sơ của khoa CNTT.

#### BR-005 — Luồng chuyển trạng thái

Các bước chuyển trạng thái xử lý hợp lệ là:

- `Chờ tiếp nhận` → `Đã tiếp nhận`.
- `Đã tiếp nhận` → `Đang xử lý`.
- `Đang xử lý` → `Đã xử lý xong`.

- `Đang xử lý` → `Cần bổ sung`.
- `Cần bổ sung` → `Đang xử lý`.
- `Đang xử lý` → `Không hợp lệ`.

Ngoài luồng trên:

- Thư ký được chuyển hồ sơ từ trạng thái chưa kết thúc sang `Đã hủy`.
- Staff chỉ được thực hiện bước `Chờ tiếp nhận` → `Đã tiếp nhận`.

Các trạng thái kết thúc là `Đã xử lý xong`, `Không hợp lệ` và `Đã hủy`.

#### BR-006 — Lưu lịch sử trạng thái

Mỗi lần trạng thái thay đổi, hệ thống phải lưu trạng thái mới, người cập nhật, thời gian cập nhật, ghi chú nếu có và `invalid_reason` dạng văn bản tự do nếu trạng thái mới là `Không hợp lệ`.

#### BR-007 — Không xóa hồ sơ đã tiếp nhận

Hồ sơ đã chuyển khỏi trạng thái `Chờ tiếp nhận` không được xóa vật lý. Hồ sơ chỉ có thể tiếp tục theo luồng trạng thái hoặc được Thư ký chuyển sang `Đã hủy`.

#### BR-008 — Xử lý phê duyệt cuối

Phê duyệt cuối được thực hiện ngoài hệ thống. Trong thời gian chờ phê duyệt, hồ sơ giữ trạng thái `Đang xử lý`. Thư ký là người cập nhật kết quả phê duyệt vào hệ thống.

#### BR-009 — Điều kiện hồ sơ không hợp lệ

Khi chuyển hồ sơ từ `Đang xử lý` sang `Không hợp lệ`, Thư ký phải kiểm tra thủ công và nhập `invalid_reason` dưới dạng văn bản tự do, tối đa 200 ký tự. `invalid_reason` không bị giới hạn trong một danh sách giá trị cố định.

Hệ thống không tự đánh giá điều kiện xử lý, điều kiện của Sinh viên hoặc tự xác định hồ sơ trùng. Hệ thống phải từ chối chuyển sang `Không hợp lệ` nếu `invalid_reason` bị bỏ trống, chỉ chứa khoảng trắng hoặc vượt quá 200 ký tự. Ghi chú bổ sung là tùy chọn và không phụ thuộc vào nội dung của `invalid_reason`.

Khi trạng thái khác `Không hợp lệ`, `invalid_reason` bắt buộc là `NULL`; client không được gửi lý do không hợp lệ cho các transition đó. Quy tắc hai chiều này phải được bảo vệ tại Service và database constraint.

#### BR-010 — Mã hồ sơ

Mã hồ sơ phải tuân theo định dạng `HS-YYYYMMDD-XXXXXXXX`, ví dụ `HS-20260728-7K4M2Q9R`, trong đó:

- `HS` là tiền tố cố định.
- `YYYYMMDD` là ngày hệ thống tạo yêu cầu theo múi giờ `Asia/Ho_Chi_Minh` (UTC+7).
- `XXXXXXXX` là chuỗi ngẫu nhiên gồm tám ký tự lấy từ tập `ABCDEFGHJKLMNPQRSTUVWXYZ23456789`. Tập ký tự không chứa `I`, `O`, `0` và `1`.

Mã hồ sơ hợp lệ phải khớp biểu thức `^HS-\d{8}-[A-HJ-NP-Z2-9]{8}$`. Mã phải do hệ thống tự sinh, duy nhất trên toàn hệ thống và không được chỉnh sửa sau khi tạo. Nếu mã vừa sinh đã tồn tại, hệ thống phải sinh mã khác trước khi lưu hồ sơ.

#### BR-011 — Quản lý dữ liệu có liên kết

Tài khoản và loại hồ sơ không được xóa vật lý; Admin phải khóa tài khoản hoặc ngừng sử dụng loại hồ sơ. Sinh viên đã liên kết với hồ sơ không được xóa vật lý; chỉ Sinh viên chưa có liên kết mới được phép xóa.

#### BR-012 — Múi giờ hệ thống

Hệ thống phải sử dụng múi giờ `Asia/Ho_Chi_Minh` (UTC+7) để hiển thị ngày giờ, xác định ngày và tháng trong báo cáo, và tạo phần `YYYYMMDD` của mã hồ sơ. Cách biểu diễn thời gian khi lưu trữ thuộc thiết kế kỹ thuật nhưng không được làm thay đổi giá trị ngày giờ hiển thị theo múi giờ này.

#### BR-013 — Idempotency của Public Submission

Mỗi lần hiển thị form Public Submission, máy chủ phải sinh một idempotency token ngẫu nhiên, gắn với session hiện tại và có hiệu lực 10 phút. Token, định danh session, hash payload, hồ sơ đã tạo và thời điểm hết hạn phải được lưu trong bảng MariaDB riêng `public_submission_idempotency`; không được chỉ lưu trong memory, file cache hoặc session payload. Token được bảo vệ bằng unique constraint, còn việc kiểm tra token và tạo hồ sơ phải nằm trong cùng transaction để hai request đồng thời chỉ tạo đúng một hồ sơ. Mọi request lặp lại trong thời hạn hiệu lực với cùng token và cùng payload phải trả lại kết quả của hồ sơ đã tạo, không tạo thêm bản ghi. Cùng token nhưng payload khác phải bị từ chối. Token hết hạn hoặc không hợp lệ không được tạo hồ sơ; Sinh viên phải tải form mới để nhận token mới. Việc chủ động gửi form mới với token mới vẫn tạo hồ sơ mới theo BR-003.

#### BR-014 — Chính sách xác thực và session nội bộ

Mật khẩu nội bộ phải có tối thiểu 8 ký tự và được băm bằng Argon2id. Không áp dụng composition rule bắt buộc; mật khẩu phổ biến hoặc đã bị lộ phải bị từ chối khi cơ chế kiểm tra tương ứng khả dụng. Đăng nhập sai không được hard-lock tài khoản hoặc tự thay đổi `users.is_active`, và phản hồi đăng nhập thất bại phải dùng thông báo lỗi chung. Session dùng `SESSION_DRIVER=database`, `SESSION_EXPIRE_ON_CLOSE=true` và `SESSION_LIFETIME=120`; vì vậy phiên hết khi đóng trình duyệt hoặc sau 120 phút không hoạt động, tùy điều kiện nào đến trước. MVP không triển khai remember-me và không dùng Redis. Admin đặt lại mật khẩu bằng một mật khẩu tạm nhưng người dùng không bắt buộc đổi mật khẩu ở lần đăng nhập kế tiếp. Admin đầu tiên phải được tạo bằng Artisan command tương tác, không có credential mặc định và command không nhận mật khẩu qua argument.

## Main Workflows

### WF-001 — Sinh viên gửi yêu cầu hồ sơ

1. Sinh viên nộp hồ sơ giấy trực tiếp cho Thư ký.
2. Sinh viên truy cập website và nhập mã sinh viên.
3. Hệ thống kiểm tra mã sinh viên.
4. Nếu mã không tồn tại, hệ thống thông báo lỗi và dừng quy trình.
5. Hệ thống hiển thị mã sinh viên và họ tên.
6. Hệ thống hiển thị các loại hồ sơ đang hoạt động và sinh idempotency token gắn với session/form theo BR-013.
7. Sinh viên chọn một loại hồ sơ và nhấn `Gửi`.
8. Với token hợp lệ chưa sử dụng, hệ thống sinh mã hồ sơ theo BR-010, lưu yêu cầu và đặt trạng thái `Chờ tiếp nhận`, kể cả khi Sinh viên đã có hồ sơ cùng loại; bước khởi tạo không tạo lịch sử trạng thái.
9. Với request lặp lại cùng token và payload, hệ thống trả lại đúng kết quả đã tạo mà không tạo hồ sơ thứ hai; token hết hạn, không hợp lệ hoặc được dùng với payload khác bị từ chối.
10. Hệ thống hiển thị kết quả gửi thành công và mã hồ sơ.

### WF-002 — Staff tiếp nhận hồ sơ

1. Staff đăng nhập.
2. Staff xem, tìm kiếm hoặc lọc danh sách hồ sơ khoa CNTT.
3. Staff mở hồ sơ ở trạng thái `Chờ tiếp nhận`.
4. Staff đối chiếu việc tiếp nhận hồ sơ giấy.
5. Staff chuyển trạng thái sang `Đã tiếp nhận`.
6. Hệ thống lưu lịch sử thay đổi.
7. Thư ký tiếp tục xử lý hồ sơ.

### WF-003 — Thư ký xử lý và cập nhật kết quả

1. Thư ký đăng nhập.
2. Thư ký xem, tìm kiếm hoặc lọc hồ sơ khoa CNTT.
3. Thư ký xác nhận tiếp nhận nếu hồ sơ vẫn ở trạng thái `Chờ tiếp nhận`.
4. Thư ký chuyển hồ sơ từ `Đã tiếp nhận` sang `Đang xử lý`; nếu hồ sơ chưa có người phụ trách, hệ thống gán Thư ký đó làm người được phân công phụ trách. Staff và các transition sau không tự động ghi đè phân công này.
5. Thư ký kiểm tra thủ công tình trạng học tập của Sinh viên. Nếu xác định Sinh viên không còn học, Thư ký nhập `invalid_reason` dạng văn bản tự do và chuyển hồ sơ sang `Không hợp lệ`.
6. Nếu cần bổ sung, Thư ký chuyển hồ sơ sang `Cần bổ sung`.
7. Sau khi có thể tiếp tục, Thư ký chuyển hồ sơ từ `Cần bổ sung` về `Đang xử lý`.
8. Thư ký kiểm tra thủ công các hồ sơ. Nếu xác định có hồ sơ trùng, Thư ký chọn hồ sơ cần xử lý, nhập `invalid_reason` dạng văn bản tự do và chuyển hồ sơ đã chọn sang `Không hợp lệ`; các hồ sơ còn lại không bị thay đổi. Thư ký có thể nhập thêm ghi chú nhưng không bắt buộc.
9. Nếu hồ sơ không trùng, Thư ký chuyển hồ sơ giấy đến phòng ban khác để phê duyệt; trạng thái trên hệ thống vẫn là `Đang xử lý`.
10. Nếu hồ sơ không đáp ứng điều kiện xử lý hoặc Sinh viên không đủ điều kiện, Thư ký nhập `invalid_reason` dạng văn bản tự do và cập nhật hồ sơ thành `Không hợp lệ`.
11. Nếu được phê duyệt, Thư ký cập nhật hồ sơ thành `Đã xử lý xong` và hệ thống ghi thời điểm hoàn tất. Các trạng thái khác không có thời điểm hoàn tất.
12. Hệ thống lưu lịch sử sau mỗi thay đổi trạng thái.

### WF-004 — Sinh viên tra cứu hồ sơ

1. Sinh viên nhập mã sinh viên; đây là input tra cứu duy nhất.
2. Hệ thống kiểm tra mã sinh viên tồn tại trong danh sách sinh viên.
3. Nếu mã không tồn tại, hệ thống thông báo lỗi và không trả về hồ sơ.
4. Nếu mã tồn tại, hệ thống tìm các hồ sơ gắn với mã sinh viên.
5. Hệ thống hiển thị toàn bộ danh sách hồ sơ và các trường được quy định tại FR-009 ngay trên kết quả tra cứu.
6. Hệ thống không yêu cầu mã hồ sơ và không cung cấp route/page public xem chi tiết riêng hoặc lịch sử xử lý của một hồ sơ.

### WF-005 — Admin quản trị dữ liệu

1. Admin đăng nhập.
2. Admin chọn quản lý tài khoản, loại hồ sơ, sinh viên hoặc phân quyền.
3. Hệ thống kiểm tra quyền Admin.
4. Admin thực hiện thao tác được quy định tại FR-023 đến FR-026.
5. Hệ thống từ chối xóa dữ liệu đã có liên kết theo BR-011.

### WF-006 — Admin xem báo cáo

1. Admin đăng nhập.
2. Admin mở chức năng báo cáo.
3. Admin chọn số liệu tổng hợp hoặc khoảng thời gian ngày, tháng.
4. Hệ thống tính toán và hiển thị số liệu theo FR-027 và FR-028.

## Data Requirements

Phần này mô tả dữ liệu logic cần lưu, không quy định bảng vật lý, kiểu dữ liệu, khóa, chỉ mục hoặc quan hệ triển khai.

### DR-001 — Dữ liệu Sinh viên

Hệ thống phải lưu tối thiểu: mã sinh viên dùng làm định danh, họ tên và email. Hệ thống chỉ phục vụ khoa CNTT nên không lưu lớp hoặc khoa cho từng Sinh viên; hệ thống cũng không lưu hoặc tự xác định tình trạng còn học hay thôi học.

### DR-002 — Dữ liệu Loại hồ sơ

Hệ thống phải lưu tối thiểu: định danh loại hồ sơ, tên loại hồ sơ, mô tả và trạng thái hoạt động.

### DR-003 — Dữ liệu Hồ sơ sinh viên

Hệ thống phải lưu tối thiểu: định danh hồ sơ, mã hồ sơ, sinh viên, loại hồ sơ, ngày giờ gửi, trạng thái hiện tại, `invalid_reason` dạng văn bản tự do tối đa 200 ký tự nếu trạng thái hiện tại là `Không hợp lệ` và `NULL` ở trạng thái khác, ghi chú tối đa 500 ký tự của trạng thái hiện tại, Thư ký hiện được phân công phụ trách nếu có, thời điểm hoàn tất chỉ khi trạng thái là `Đã xử lý xong`, và ngày giờ cập nhật gần nhất.

### DR-004 — Dữ liệu Lịch sử xử lý

Hệ thống phải lưu tối thiểu: định danh lịch sử, hồ sơ, trạng thái mới, `invalid_reason` dạng văn bản tự do tối đa 200 ký tự nếu trạng thái mới là `Không hợp lệ` và `NULL` ở trạng thái khác, snapshot ghi chú bất biến tối đa 500 ký tự nếu có, người cập nhật và ngày giờ cập nhật.

### DR-005 — Dữ liệu Tài khoản

Hệ thống phải lưu tối thiểu: định danh tài khoản, tên đăng nhập, thông tin xác thực được bảo vệ, vai trò và trạng thái tài khoản.

### DR-006 — Tính toàn vẹn mã

Mã sinh viên phải xác định duy nhất một Sinh viên. Mã hồ sơ phải xác định duy nhất một hồ sơ.

### DR-007 — Bảo toàn dữ liệu lịch sử

Việc khóa tài khoản, ngừng sử dụng loại hồ sơ hoặc chỉnh sửa thông tin Sinh viên không được làm mất các liên kết đã được lưu trong hồ sơ và lịch sử xử lý.

### DR-008 — Dữ liệu session và idempotency

Session nội bộ phải được lưu trong MariaDB. Dữ liệu idempotency của Public Submission phải được lưu trong bảng riêng `public_submission_idempotency`, tối thiểu gồm token duy nhất, định danh session, hash payload, tham chiếu hồ sơ đã tạo và thời điểm hết hạn.

## Non-functional Requirements

MVP triển khai trên một application instance. Session và idempotency dùng MariaDB để thiết kế không ngăn cản việc nâng cấp nhiều instance sau MVP.

Các NFR dưới đây chưa có chỉ tiêu định lượng trong SRS. Chúng được giữ ở trạng thái `TBD – cần xác nhận` theo DG-005 và chưa thể dùng để kết luận hệ thống đạt nghiệm thu cho đến khi có giá trị cụ thể. DG-005 không chặn triển khai chức năng P0–P8 nhưng bắt buộc phải được chốt trước P9/production.

#### NFR-001 — Thời gian phản hồi

`TBD – cần xác nhận`: thời gian phản hồi tối đa và phân vị đo cho thao tác kiểm tra mã sinh viên, gửi yêu cầu, tra cứu, tìm kiếm/lọc và cập nhật trạng thái.

#### NFR-002 — Tải đồng thời

`TBD – cần xác nhận`: số lượng người dùng đồng thời và khối lượng yêu cầu tối đa hệ thống phải đáp ứng.

#### NFR-003 — Độ sẵn sàng

`TBD – cần xác nhận`: tỷ lệ sẵn sàng theo tháng, khung giờ vận hành và thời gian bảo trì cho phép.

#### NFR-004 — Khôi phục sau sự cố

`TBD – cần xác nhận`: mục tiêu thời gian khôi phục (RTO) và mức mất dữ liệu tối đa cho phép (RPO).

#### NFR-005 — Thời gian lưu trữ

`TBD – cần xác nhận`: thời hạn lưu hồ sơ, lịch sử xử lý, tài khoản bị khóa và dữ liệu danh mục ngừng hoạt động.

#### NFR-006 — Khả năng tương thích

`TBD – cần xác nhận`: danh sách trình duyệt, phiên bản tối thiểu và kích thước màn hình phải hỗ trợ.

#### NFR-007 — Khả năng tiếp cận

`TBD – cần xác nhận`: tiêu chuẩn và cấp độ khả năng tiếp cận phải đáp ứng.

## Security Requirements

#### SEC-001 — Xác thực người dùng nội bộ

Staff, Thư ký và Admin phải đăng nhập thành công trước khi truy cập chức năng nội bộ.

#### SEC-002 — Phân quyền phía máy chủ

Mỗi yêu cầu truy cập hoặc thay đổi dữ liệu nội bộ phải được kiểm tra quyền theo vai trò ở phía máy chủ. Việc chỉ ẩn chức năng trên giao diện không được xem là đáp ứng yêu cầu này.

#### SEC-003 — Chặn truy cập trái quyền

Người chưa đăng nhập hoặc tài khoản không có quyền phải bị từ chối khi truy cập chức năng Admin, Staff hoặc Thư ký.

#### SEC-004 — Giới hạn quyền cập nhật của Staff

Hệ thống phải từ chối mọi yêu cầu của Staff nhằm chuyển trạng thái ngoài bước `Chờ tiếp nhận` → `Đã tiếp nhận`.

#### SEC-005 — Bảo vệ thông tin xác thực

Thông tin xác thực không được lưu dưới dạng văn bản thuần. Mật khẩu phải có tối thiểu 8 ký tự, được băm bằng Argon2id, không có composition rule bắt buộc và phải bị từ chối nếu phổ biến/đã bị lộ khi cơ chế kiểm tra khả dụng. Đăng nhập sai không được hard-lock tài khoản hoặc tự đổi `users.is_active`; mọi trường hợp đăng nhập thất bại phải trả thông báo lỗi chung, không tiết lộ username, mật khẩu hay trạng thái tài khoản nào sai. Admin có thể cấp mật khẩu tạm khi reset và người dùng không bắt buộc đổi ở lần đăng nhập tiếp theo. Admin đầu tiên chỉ được tạo bằng Artisan command tương tác; không có credential mặc định và command không nhận mật khẩu qua argument. Session nội bộ cấu hình `SESSION_DRIVER=database`, `SESSION_EXPIRE_ON_CLOSE=true`, `SESSION_LIFETIME=120`, không dùng Redis và không hỗ trợ remember-me trong MVP.

#### SEC-006 — Tra cứu công khai của Sinh viên

Chức năng tra cứu chỉ dùng mã sinh viên và không yêu cầu đăng nhập, mã hồ sơ, OTP, CAPTCHA hoặc yếu tố xác minh thứ hai. Đây là quyết định truy cập đã được chấp nhận cho MVP. Hệ thống trả danh sách hồ sơ thuộc đúng mã sinh viên được nhập, chỉ gồm thông tin cơ bản tại FR-002 và các trường tại FR-009; không cung cấp public detail endpoint hoặc lịch sử xử lý. HTTPS, validation và rate limit chung vẫn được áp dụng như kiểm soát nền tảng/khả dụng, không phải bước xác minh danh tính.

## Status Definitions

| Trạng thái | Định nghĩa | Trạng thái kết thúc |
|---|---|---|
| `Chờ tiếp nhận` | Yêu cầu đã được tạo nhưng chưa được Staff hoặc Thư ký xác nhận nhận hồ sơ giấy | Không |
| `Đã tiếp nhận` | Staff hoặc Thư ký đã xác nhận nhận hồ sơ giấy | Không |
| `Đang xử lý` | Thư ký đang xử lý hoặc hồ sơ giấy đang được phòng ban khác phê duyệt ngoài hệ thống | Không |
| `Cần bổ sung` | Hồ sơ cần thêm thông tin hoặc giấy tờ trước khi tiếp tục xử lý | Không |
| `Đã xử lý xong` | Hồ sơ đã được phê duyệt và hoàn tất toàn bộ quá trình xử lý | Có |
| `Không hợp lệ` | Thư ký đã kiểm tra thủ công và nhập lý do không hợp lệ dưới dạng văn bản tự do | Có |
| `Đã hủy` | Hồ sơ đã bị Thư ký hủy theo quyền được quy định | Có |

## Acceptance Criteria

### AC-FR-001 — Kiểm tra và hiển thị Sinh viên

- Với mã sinh viên tồn tại, hệ thống trả về đúng mã sinh viên và họ tên của bản ghi tương ứng.
- Với mã sinh viên không tồn tại, hệ thống hiển thị lỗi, không cho phép gửi yêu cầu và không trả về danh sách hồ sơ.

### AC-FR-002 — Gửi yêu cầu hồ sơ

- Với mã sinh viên tồn tại và loại hồ sơ đang hoạt động, một lần nhấn `Gửi` tạo đúng một hồ sơ, kể cả khi đã tồn tại hồ sơ cùng loại.
- Form Public Submission nhận idempotency token do máy chủ sinh, gắn với session và có hiệu lực 10 phút.
- Hai request có cùng token và cùng payload trả về cùng một hồ sơ; database chỉ có một bản ghi mới.
- Request dùng lại token với payload khác, token hết hạn hoặc token không hợp lệ bị từ chối và không tạo hồ sơ.
- Form mới cung cấp token mới; gửi hợp lệ bằng token mới vẫn tạo hồ sơ mới theo BR-003.
- Hồ sơ mới chứa đúng Sinh viên, loại hồ sơ, thời gian gửi, mã hồ sơ duy nhất và trạng thái `Chờ tiếp nhận`.
- Mã hồ sơ mới khớp biểu thức `^HS-\d{8}-[A-HJ-NP-Z2-9]{8}$`; phần `YYYYMMDD` bằng ngày tạo yêu cầu theo múi giờ `Asia/Ho_Chi_Minh` (UTC+7).
- Hai hồ sơ bất kỳ không có cùng mã hồ sơ.
- Khi bộ sinh mã trả về một mã đã tồn tại, hệ thống không lưu mã trùng và sinh mã khác trước khi tạo hồ sơ.
- Sau khi hồ sơ được tạo, yêu cầu thay đổi mã hồ sơ bị hệ thống từ chối.
- Public Submission không tạo bản ghi `document_status_history`.

### AC-FR-003 — Gửi và xử lý hồ sơ trùng

- Khi Sinh viên đã có một hồ sơ cùng loại ở bất kỳ trạng thái nào, một lần gửi hợp lệ tiếp theo vẫn tạo đúng một hồ sơ mới có mã hồ sơ riêng và trạng thái `Chờ tiếp nhận`.
- Việc tồn tại nhiều hồ sơ cùng Sinh viên và cùng loại không làm hệ thống tự động chuyển bất kỳ hồ sơ nào sang `Không hợp lệ`.
- Khi có nhiều hồ sơ cùng loại, Thư ký chọn được một hồ sơ cụ thể để xử lý là hồ sơ trùng.
- Khi Thư ký chọn hồ sơ cần xử lý và nhập `invalid_reason` có ít nhất một ký tự không phải khoảng trắng, không vượt quá 200 ký tự, hệ thống cho phép chuyển hồ sơ đã chọn từ `Đang xử lý` sang `Không hợp lệ`; trạng thái các hồ sơ còn lại không thay đổi.
- Khi Thư ký không nhập `invalid_reason`, chỉ nhập khoảng trắng hoặc nhập quá 200 ký tự, hệ thống từ chối chuyển hồ sơ đã chọn sang `Không hợp lệ` và giữ nguyên trạng thái hiện tại.
- Sau khi hồ sơ trùng được chuyển sang `Không hợp lệ`, Sinh viên tra cứu được trạng thái, `invalid_reason` và ghi chú bổ sung nếu Thư ký đã nhập.

### AC-FR-004 — Tra cứu hồ sơ

- Khi nhập một mã sinh viên có hồ sơ, hệ thống chỉ trả về các hồ sơ gắn với mã đó.
- Mã sinh viên là input tra cứu duy nhất; request không yêu cầu `document_code`, đăng nhập, OTP, CAPTCHA hoặc yếu tố xác minh thứ hai.
- Mỗi kết quả hiển thị đủ mã hồ sơ, loại hồ sơ, ngày gửi, trạng thái hiện tại, `invalid_reason` nếu trạng thái là `Không hợp lệ`, ghi chú bổ sung nếu có và ngày cập nhật gần nhất.
- Nếu lần chuyển trạng thái hiện tại có ghi chú, kết quả tra cứu hiển thị đúng ghi chú đó. Nếu lần chuyển mới không có ghi chú, kết quả không hiển thị lại ghi chú của trạng thái trước. Ghi chú tối đa 500 ký tự và không ảnh hưởng đến việc kiểm tra `invalid_reason`.
- Sinh viên không có route/page public xem chi tiết riêng một hồ sơ và không có giao diện hoặc API để xem danh sách lịch sử thay đổi trạng thái.

### AC-FR-005 — Đăng nhập và phân quyền

- Staff, Thư ký và Admin có thông tin xác thực hợp lệ đăng nhập được.
- Thông tin xác thực sai không tạo phiên đăng nhập.
- Mọi trường hợp đăng nhập thất bại hiển thị cùng một thông báo lỗi chung và không tiết lộ username, mật khẩu, trạng thái kích hoạt hay nguyên nhân cụ thể nào sai.
- Người chưa đăng nhập bị từ chối khi gọi chức năng nội bộ.
- Tài khoản của từng vai trò bị từ chối khi gọi trực tiếp chức năng không thuộc quyền của vai trò đó.
- Session nội bộ dùng `SESSION_DRIVER=database`, `SESSION_EXPIRE_ON_CLOSE=true`, `SESSION_LIFETIME=120`; phiên hết khi đóng trình duyệt hoặc sau 120 phút không hoạt động. Hệ thống không dùng Redis và không cung cấp remember-me trong MVP.
- Đăng nhập sai không hard-lock tài khoản và không tự thay đổi `users.is_active`.

### AC-FR-006 — Quyền Staff

- Staff xem, tìm kiếm và lọc được hồ sơ theo từng tiêu chí quy định tại FR-013.
- Staff xem được chi tiết và các bản ghi lịch sử của hồ sơ.
- Staff chuyển được hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận`.
- Staff bị từ chối khi thử thực hiện bất kỳ bước chuyển trạng thái nào khác.

### AC-FR-007 — Quyền Thư ký và luồng trạng thái

- Thư ký thực hiện được từng bước chuyển hợp lệ tại BR-005.
- Mỗi bước chuyển không được liệt kê tại BR-005 bị hệ thống từ chối, ngoại trừ quyền hủy tại FR-021.
- Hồ sơ giữ trạng thái `Đang xử lý` trong thời gian chờ phòng ban khác phê duyệt.
- Sau kết quả phê duyệt hợp lệ, Thư ký chuyển được hồ sơ sang trạng thái kết thúc `Đã xử lý xong`.
- Khi nhập `invalid_reason` có ít nhất một ký tự không phải khoảng trắng và không vượt quá 200 ký tự, Thư ký chuyển được hồ sơ từ `Đang xử lý` sang `Không hợp lệ`.
- `invalid_reason` không bị giới hạn trong một danh sách giá trị cố định.
- Khi không nhập `invalid_reason`, chỉ nhập khoảng trắng hoặc nhập quá 200 ký tự, hệ thống từ chối chuyển hồ sơ từ `Đang xử lý` sang `Không hợp lệ`.
- Khi trạng thái mới khác `Không hợp lệ`, hệ thống từ chối input có `invalid_reason` và dữ liệu lưu phải có `invalid_reason = NULL`.
- Hệ thống không tự xác định hồ sơ có đáp ứng điều kiện xử lý, Sinh viên còn học, Sinh viên có đủ điều kiện hoặc hồ sơ có bị trùng hay không.
- Ghi chú bổ sung không bắt buộc và nội dung ghi chú không ảnh hưởng đến việc cho phép chuyển hồ sơ sang `Không hợp lệ`.
- Khi Thư ký đầu tiên chuyển hồ sơ từ `Đã tiếp nhận` sang `Đang xử lý`, hồ sơ chưa được phân công được gán cho Thư ký đó; Staff và các transition tiếp theo không tự động ghi đè người được phân công.
- Mọi Thư ký đang hoạt động thực hiện được transition hợp lệ trên hồ sơ được phân công cho Thư ký khác; assignment không phải authorization độc quyền và người được phân công không bị tự động ghi đè.
- `completed_at` được đặt khi chuyển sang `Đã xử lý xong`; tất cả trạng thái khác có `completed_at = NULL`.

### AC-FR-008 — Lưu lịch sử

- Sau mỗi lần đổi trạng thái thành công, hệ thống tạo đúng một bản ghi lịch sử chứa trạng thái mới, người cập nhật, thời gian cập nhật, ghi chú bổ sung nếu có và `invalid_reason` dạng văn bản tự do nếu trạng thái mới là `Không hợp lệ`; thời gian hiển thị theo `Asia/Ho_Chi_Minh` (UTC+7).
- Bản ghi lịch sử đầu tiên được tạo khi Staff hoặc Thư ký chuyển hồ sơ từ `Chờ tiếp nhận` sang `Đã tiếp nhận`; hồ sơ vừa được Public Submission tạo chưa có lịch sử.
- Một lần chuyển trạng thái bị từ chối không tạo bản ghi lịch sử.
- Ghi chú và các trường của lịch sử cũ không thay đổi khi hồ sơ tiếp tục chuyển trạng thái.

### AC-FR-009 — Quản lý tài khoản

- Admin thêm, xem, sửa, khóa và mở khóa được tài khoản Staff/Thư ký.
- Hệ thống không cung cấp thao tác hoặc endpoint hard delete tài khoản.
- Tài khoản bị khóa vẫn được giữ nguyên các liên kết lịch sử đã phát sinh.
- Hệ thống từ chối khóa hoặc đổi vai trò Thư ký còn phụ trách hồ sơ chưa kết thúc nếu chưa tái phân công toàn bộ hồ sơ mở cho Thư ký đang hoạt động khác.
- Tái phân công và khóa/đổi vai trò phải thành công hoặc rollback cùng nhau; không để hồ sơ mở tham chiếu một người chịu trách nhiệm chính không còn là Thư ký đang hoạt động.
- Mật khẩu mới và mật khẩu tạm phải có tối thiểu 8 ký tự, được băm bằng Argon2id và không được lưu hoặc ghi log dưới dạng văn bản thuần. Không bắt buộc chữ hoa, chữ thường, chữ số hoặc ký tự đặc biệt; mật khẩu phổ biến/đã bị lộ bị từ chối khi cơ chế kiểm tra khả dụng.
- Admin reset được mật khẩu thành mật khẩu tạm; người dùng đăng nhập được bằng mật khẩu tạm mà không bị bắt buộc đổi mật khẩu ở lần đăng nhập tiếp theo.
- Admin đầu tiên được tạo bằng Artisan command tương tác; command không có credential mặc định và từ chối/không khai báo password argument.

### AC-FR-010 — Quản lý loại hồ sơ và Sinh viên

- Admin thêm, xem, sửa, kích hoạt và ngừng sử dụng được loại hồ sơ; hệ thống không cung cấp hard delete loại hồ sơ. Admin đồng thời thêm, xem, sửa và xóa được Sinh viên chưa liên kết với hồ sơ.
- Loại hồ sơ ngừng hoạt động không xuất hiện trong danh sách cho Sinh viên chọn.
- Sinh viên có mã tồn tại được phép gửi yêu cầu; tình trạng còn học hay thôi học do Thư ký kiểm tra thủ công trong quá trình xử lý.
- Hệ thống từ chối xóa Sinh viên đã được liên kết với hồ sơ; loại hồ sơ chỉ được ngừng sử dụng, không được xóa vật lý.

### AC-FR-011 — Báo cáo trạng thái, loại hồ sơ và thời gian

- Báo cáo trả đủ bảy số liệu trạng thái: `Chờ tiếp nhận`, `Đã tiếp nhận`, `Đang xử lý`, `Cần bổ sung`, `Đã xử lý xong`, `Không hợp lệ`, `Đã hủy`.
- Mỗi số liệu trạng thái bằng số hồ sơ hiện có đúng trạng thái tương ứng trong tập dữ liệu báo cáo.
- Số hồ sơ theo từng loại bằng số hồ sơ liên kết với loại đó trong tập dữ liệu báo cáo.
- Báo cáo theo ngày và tháng chỉ đếm hồ sơ có ngày gửi thuộc ngày hoặc tháng được chọn theo múi giờ `Asia/Ho_Chi_Minh` (UTC+7).

### AC-NFR-001 — Xác nhận chỉ tiêu phi chức năng

- Trước khi kiểm thử hiệu năng và vận hành, mỗi NFR-001 đến NFR-007 phải có giá trị đo, điều kiện đo và ngưỡng đạt được phê duyệt.
- NFR chưa có đủ ba thành phần trên được ghi nhận là `Chưa thể kiểm thử`, không được ghi nhận là `Đạt`.

### AC-UI-001 — Phân chia và kiểm soát giao diện

- Đăng nhập và các màn hình nội bộ Staff, Thư ký, Admin render bằng React + TypeScript/Inertia trong cùng Laravel application.
- Public Submission/Public Lookup, trang lỗi và Inertia shell render bằng Blade.
- Inertia mutation dùng web route, session và CSRF; gọi trực tiếp một action trái quyền vẫn bị máy chủ từ chối dù trạng thái giao diện phía client như thế nào.
- Frontend build phải qua strict type-check, component test và production build trước khi release.

### AC-SEC-001 — Bảo vệ truy cập

- Yêu cầu không có phiên đăng nhập hợp lệ đến chức năng nội bộ bị từ chối.
- Staff gọi trực tiếp chức năng cập nhật trạng thái ngoài quyền tại FR-015 bị từ chối.
- Thư ký không truy cập được chức năng quản trị dành riêng cho Admin.
- Dữ liệu thông tin xác thực được kiểm tra và không chứa mật khẩu dạng văn bản thuần.

## Open Questions

| ID | Nội dung cần xác nhận | Ảnh hưởng |
|---|---|---|
| OQ-001 | Thời gian phản hồi tối đa và phân vị đo cho NFR-001 là bao nhiêu? | Kiểm thử hiệu năng |
| OQ-002 | Số người dùng đồng thời và khối lượng yêu cầu mục tiêu cho NFR-002 là bao nhiêu? | Kiểm thử tải |
| OQ-003 | Tỷ lệ sẵn sàng, khung giờ vận hành và thời gian bảo trì cho NFR-003 là bao nhiêu? | Vận hành |
| OQ-004 | RTO và RPO cho NFR-004 là bao nhiêu? | Sao lưu và khôi phục |
| OQ-005 | Thời hạn lưu từng nhóm dữ liệu cho NFR-005 là bao nhiêu? | Lưu trữ và tuân thủ |
| OQ-006 | Trình duyệt, phiên bản và kích thước màn hình tối thiểu cho NFR-006 là gì? | Kiểm thử tương thích |
| OQ-007 | Tiêu chuẩn và cấp độ khả năng tiếp cận cho NFR-007 là gì? | Kiểm thử khả năng tiếp cận |

## Requirements Traceability Matrix

| Requirement ID | Requirement Name | User Role | Related Workflow | Acceptance Criteria |
|---|---|---|---|---|
| FR-001 | Kiểm tra mã sinh viên | Sinh viên | WF-001 | AC-FR-001 |
| FR-002 | Hiển thị thông tin sinh viên | Sinh viên | WF-001 | AC-FR-001 |
| FR-003 | Thông báo mã sinh viên không tồn tại | Sinh viên | WF-001 | AC-FR-001 |
| FR-004 | Hiển thị loại hồ sơ được tiếp nhận | Sinh viên | WF-001 | AC-FR-002, AC-FR-010 |
| FR-005 | Gửi yêu cầu hồ sơ | Sinh viên | WF-001 | AC-FR-002 |
| FR-006 | Khởi tạo yêu cầu hồ sơ | Sinh viên, Hệ thống | WF-001 | AC-FR-002 |
| FR-007 | Cho phép gửi nhiều hồ sơ cùng loại | Sinh viên, Hệ thống | WF-001 | AC-FR-003 |
| FR-008 | Tra cứu danh sách hồ sơ | Sinh viên | WF-004 | AC-FR-004 |
| FR-009 | Hiển thị thông tin tra cứu hồ sơ | Sinh viên | WF-004 | AC-FR-004 |
| FR-010 | Đăng nhập | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-FR-005 |
| FR-011 | Kiểm soát truy cập theo vai trò | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-FR-005, AC-SEC-001 |
| FR-012 | Xem danh sách hồ sơ | Staff | WF-002 | AC-FR-006 |
| FR-013 | Tìm kiếm và lọc hồ sơ | Staff | WF-002 | AC-FR-006 |
| FR-014 | Xem chi tiết và lịch sử hồ sơ | Staff | WF-002 | AC-FR-006 |
| FR-015 | Staff xác nhận tiếp nhận hồ sơ | Staff | WF-002 | AC-FR-006 |
| FR-016 | Xem và tra cứu hồ sơ | Thư ký | WF-003 | AC-FR-007 |
| FR-017 | Thư ký xác nhận tiếp nhận hồ sơ | Thư ký | WF-003 | AC-FR-007 |
| FR-018 | Cập nhật trạng thái xử lý | Thư ký | WF-003 | AC-FR-003, AC-FR-007 |
| FR-019 | Nhập ghi chú xử lý | Thư ký | WF-003 | AC-FR-003, AC-FR-007, AC-FR-008 |
| FR-020 | Cập nhật kết quả phê duyệt ngoài hệ thống | Thư ký | WF-003 | AC-FR-007 |
| FR-021 | Thư ký hủy hồ sơ | Thư ký | WF-003 | AC-FR-007 |
| FR-022 | Lưu lịch sử trạng thái | Hệ thống | WF-002, WF-003 | AC-FR-008 |
| FR-023 | Quản lý tài khoản nội bộ | Admin | WF-005 | AC-FR-009 |
| FR-024 | Phân quyền tài khoản | Admin | WF-005 | AC-FR-005, AC-FR-009 |
| FR-025 | Quản lý loại hồ sơ | Admin | WF-005 | AC-FR-010 |
| FR-026 | Quản lý danh sách sinh viên | Admin | WF-005 | AC-FR-010 |
| FR-027 | Xem thống kê đầy đủ bảy trạng thái | Admin | WF-006 | AC-FR-011 |
| FR-028 | Xem thống kê theo thời gian | Admin | WF-006 | AC-FR-011 |
| BR-001 | Mã sinh viên phải tồn tại | Sinh viên | WF-001, WF-004 | AC-FR-001, AC-FR-004 |
| BR-002 | Loại hồ sơ phải được phép tiếp nhận | Sinh viên, Admin | WF-001, WF-005 | AC-FR-002, AC-FR-010 |
| BR-003 | Xử lý hồ sơ gửi trùng | Sinh viên, Thư ký | WF-001, WF-003 | AC-FR-003 |
| BR-004 | Phạm vi hồ sơ nội bộ | Staff, Thư ký | WF-002, WF-003 | AC-FR-006, AC-FR-007 |
| BR-005 | Luồng chuyển trạng thái | Staff, Thư ký | WF-002, WF-003 | AC-FR-006, AC-FR-007 |
| BR-006 | Lưu lịch sử trạng thái | Hệ thống | WF-002, WF-003 | AC-FR-008 |
| BR-007 | Không xóa hồ sơ đã tiếp nhận | Thư ký, Hệ thống | WF-003 | AC-FR-007 |
| BR-008 | Xử lý phê duyệt cuối | Thư ký | WF-003 | AC-FR-007 |
| BR-009 | Điều kiện hồ sơ không hợp lệ | Thư ký | WF-003 | AC-FR-003, AC-FR-007 |
| BR-010 | Mã hồ sơ | Hệ thống | WF-001 | AC-FR-002 |
| BR-011 | Quản lý dữ liệu có liên kết | Admin | WF-005 | AC-FR-009, AC-FR-010 |
| BR-012 | Múi giờ hệ thống | Tất cả | WF-001, WF-002, WF-003, WF-004, WF-006 | AC-FR-002, AC-FR-008, AC-FR-011 |
| BR-013 | Idempotency của Public Submission | Sinh viên, Hệ thống | WF-001 | AC-FR-002 |
| BR-014 | Chính sách xác thực và session nội bộ | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-FR-005, AC-FR-009, AC-SEC-001 |
| NFR-001 | Thời gian phản hồi | Tất cả | Tất cả | AC-NFR-001 |
| NFR-002 | Tải đồng thời | Tất cả | Tất cả | AC-NFR-001 |
| NFR-003 | Độ sẵn sàng | Tất cả | Tất cả | AC-NFR-001 |
| NFR-004 | Khôi phục sau sự cố | Admin, Vận hành | Không áp dụng | AC-NFR-001 |
| NFR-005 | Thời gian lưu trữ | Admin, Vận hành | Không áp dụng | AC-NFR-001 |
| NFR-006 | Khả năng tương thích | Tất cả | Tất cả | AC-NFR-001 |
| NFR-007 | Khả năng tiếp cận | Tất cả | Tất cả | AC-NFR-001 |
| UI-001 | React cho khu vực nội bộ | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-UI-001 |
| UI-002 | Blade cho khu vực công khai và trang lỗi | Sinh viên, Tất cả | WF-001, WF-004 | AC-UI-001 |
| UI-003 | Máy chủ là nguồn quyết định nghiệp vụ | Tất cả | Tất cả | AC-UI-001, AC-SEC-001 |
| SEC-001 | Xác thực người dùng nội bộ | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-FR-005, AC-SEC-001 |
| SEC-002 | Phân quyền phía máy chủ | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-SEC-001 |
| SEC-003 | Chặn truy cập trái quyền | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-FR-005, AC-SEC-001 |
| SEC-004 | Giới hạn quyền cập nhật của Staff | Staff | WF-002 | AC-FR-006, AC-SEC-001 |
| SEC-005 | Bảo vệ thông tin xác thực | Staff, Thư ký, Admin | WF-002, WF-003, WF-005, WF-006 | AC-SEC-001 |
| SEC-006 | Tra cứu công khai của Sinh viên | Sinh viên | WF-001, WF-004 | AC-FR-001, AC-FR-004 |

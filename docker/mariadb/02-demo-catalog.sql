-- Demo catalog only. No real student records.
SET NAMES utf8mb4;
SET time_zone = '+00:00';

INSERT INTO `document_statuses` (`code`, `label`, `badge_class`, `color_hex`, `sort_order`, `is_system`, `is_active`, `created_at`, `updated_at`) VALUES
('waiting_for_receipt', 'Chờ tiếp nhận', 'badge-cho', '#1e4fd6', 1, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('received', 'Đã tiếp nhận', 'badge-tiep-nhan', '#16a34a', 2, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('processing', 'Đang xử lý', 'badge-dang-xu-ly', '#f59e0b', 3, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('needs_supplement', 'Cần bổ sung', 'badge-bo-sung', '#eab308', 4, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('completed', 'Hoàn tất', 'badge-hoan-tat', '#7c3aed', 5, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('invalid', 'Không hợp lệ', 'badge-khong-hop-le', '#dc2626', 6, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()),
('cancelled', 'Đã hủy', 'badge-huy', '#6b7280', 7, 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP());

INSERT INTO `document_types` (`code`, `name`, `description`, `is_active`) VALUES
('XNSV', 'Giấy xác nhận sinh viên', 'Loại hồ sơ demo', 1),
('BDHT', 'Bảng điểm học tập', 'Loại hồ sơ demo', 1);

-- Demo password for all accounts: password
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `email`, `role`, `is_active`) VALUES
('admin', '$2y$12$nkPtvQuCzZzuXa6Lp3VukeqFMsz.L7bOlPvgX5RNdSMQq3Mi0QLuG', 'Quản trị viên demo', 'admin@example.test', 'admin', 1),
('secretary', '$2y$12$nkPtvQuCzZzuXa6Lp3VukeqFMsz.L7bOlPvgX5RNdSMQq3Mi0QLuG', 'Thư ký demo', 'secretary@example.test', 'secretary', 1),
('staff', '$2y$12$nkPtvQuCzZzuXa6Lp3VukeqFMsz.L7bOlPvgX5RNdSMQq3Mi0QLuG', 'Nhân viên demo', 'staff@example.test', 'staff', 1);

INSERT INTO `students` (`student_code`, `last_name`, `first_name`, `date_of_birth`, `phone_number`, `email`) VALUES
('DEMO0001', 'Nguyễn Văn', 'An', '2004-01-15', NULL, NULL);

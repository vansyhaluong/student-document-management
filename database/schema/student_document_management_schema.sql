-- Sanitized schema-only baseline for version control.
-- Không chứa dữ liệu sinh viên hoặc dữ liệu vận hành thực tế.
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 29, 2026 lúc 04:00 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cấu trúc bảng cho bảng `document_status_history`
--

CREATE TABLE `document_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('waiting_for_receipt','received','processing','needs_supplement','completed','invalid','cancelled') NOT NULL,
  `invalid_reason` varchar(200) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `changed_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `document_types`
--

CREATE TABLE `document_types` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--


-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `student_code` varchar(20) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--


-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student_documents`
--

CREATE TABLE `student_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_code` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `student_code` varchar(20) NOT NULL,
  `document_type_id` smallint(5) UNSIGNED NOT NULL,
  `status` enum('waiting_for_receipt','received','processing','needs_supplement','completed','invalid','cancelled') NOT NULL DEFAULT 'waiting_for_receipt',
  `assigned_secretary_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `invalid_reason` varchar(200) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `student_documents`
--
DELIMITER $$
CREATE TRIGGER `trg_student_documents_document_code_immutable` BEFORE UPDATE ON `student_documents` FOR EACH ROW BEGIN
  IF NOT (NEW.`document_code` <=> OLD.`document_code`) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'document_code cannot be changed after creation';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('staff','secretary','admin') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng
--

--
-- Chỉ mục cho bảng `document_status_history`
--
ALTER TABLE `document_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_document_status_history_student_document_id_changed_at` (`student_document_id`,`changed_at`),
  ADD KEY `idx_document_status_history_changed_by_user_id` (`changed_by_user_id`);

--
-- Chỉ mục cho bảng `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_document_types_code` (`code`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_code`),
  ADD UNIQUE KEY `uq_students_email` (`email`),
  ADD KEY `idx_students_last_name_first_name` (`last_name`,`first_name`);

--
-- Chỉ mục cho bảng `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_documents_document_code` (`document_code`),
  ADD KEY `idx_student_documents_student_code_document_type_id` (`student_code`,`document_type_id`),
  ADD KEY `idx_student_documents_status` (`status`),
  ADD KEY `idx_student_documents_document_type_id_status` (`document_type_id`,`status`),
  ADD KEY `idx_student_documents_submitted_at` (`submitted_at`),
  ADD KEY `idx_student_documents_assigned_secretary_user_id` (`assigned_secretary_user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng
--

--
-- AUTO_INCREMENT cho bảng `document_status_history`
--
ALTER TABLE `document_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng
--

--
-- Các ràng buộc cho bảng `document_status_history`
--
ALTER TABLE `document_status_history`
  ADD CONSTRAINT `fk_document_status_history_student_document_id` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_status_history_changed_by_user_id` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `chk_document_status_history_invalid_reason_matches_status` CHECK ((`status` = 'invalid' AND `invalid_reason` IS NOT NULL AND CHAR_LENGTH(TRIM(`invalid_reason`)) BETWEEN 1 AND 200) OR (`status` <> 'invalid' AND `invalid_reason` IS NULL));

--
-- Các ràng buộc cho bảng `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `fk_student_documents_assigned_secretary_user_id` FOREIGN KEY (`assigned_secretary_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_documents_document_type_id` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_documents_student_code` FOREIGN KEY (`student_code`) REFERENCES `students` (`student_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `chk_student_documents_invalid_reason_matches_status` CHECK ((`status` = 'invalid' AND `invalid_reason` IS NOT NULL AND CHAR_LENGTH(TRIM(`invalid_reason`)) BETWEEN 1 AND 200) OR (`status` <> 'invalid' AND `invalid_reason` IS NULL)),
  ADD CONSTRAINT `chk_student_documents_completed_at_matches_status` CHECK ((`status` = 'completed' AND `completed_at` IS NOT NULL) OR (`status` <> 'completed' AND `completed_at` IS NULL));
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

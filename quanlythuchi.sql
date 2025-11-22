-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 22, 2025 lúc 01:02 AM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlythuchi`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `type`, `created_at`) VALUES
(1, 1, 'Lương chính', 'income', '2025-11-06 08:45:15'),
(2, 1, 'Tiền thưởng', 'income', '2025-11-06 08:45:15'),
(3, 1, 'Làm thêm giờ', 'income', '2025-11-06 08:45:15'),
(4, 1, 'Thu nhập khác', 'income', '2025-11-06 08:45:15'),
(5, 1, 'Bán hàng', 'income', '2025-11-06 08:45:15'),
(6, 1, 'Tiền lãi tiết kiệm', 'income', '2025-11-06 08:45:15'),
(7, 1, 'Cổ tức / đầu tư', 'income', '2025-11-06 08:45:15'),
(8, 1, 'Tiền trợ cấp', 'income', '2025-11-06 08:45:15'),
(9, 1, 'Quà tặng / biếu', 'income', '2025-11-06 08:45:15'),
(10, 1, 'Lương chính', 'income', '2025-11-06 08:45:15'),
(11, 1, 'Tiền thưởng', 'income', '2025-11-06 08:45:15'),
(12, 1, 'Làm thêm giờ', 'income', '2025-11-06 08:45:15'),
(13, 1, 'Thu nhập khác', 'income', '2025-11-06 08:45:15'),
(14, 1, 'Bán hàng', 'income', '2025-11-06 08:45:15'),
(15, 1, 'Tiền lãi tiết kiệm', 'income', '2025-11-06 08:45:15'),
(16, 1, 'Cổ tức / đầu tư', 'income', '2025-11-06 08:45:15'),
(17, 1, 'Tiền trợ cấp', 'income', '2025-11-06 08:45:15'),
(18, 1, 'Quà tặng / biếu', 'income', '2025-11-06 08:45:15'),
(19, 1, 'Ăn uống', 'expense', '2025-11-06 08:45:34'),
(20, 1, 'Đi lại', 'expense', '2025-11-06 08:45:34'),
(21, 1, 'Mua sắm', 'expense', '2025-11-06 08:45:34'),
(22, 1, 'Tiền điện, nước, internet', 'expense', '2025-11-06 08:45:34'),
(23, 1, 'Thuê nhà', 'expense', '2025-11-06 08:45:34'),
(24, 1, 'Học tập / giáo dục', 'expense', '2025-11-06 08:45:34'),
(25, 1, 'Giải trí', 'expense', '2025-11-06 08:45:34'),
(26, 1, 'Sức khỏe / y tế', 'expense', '2025-11-06 08:45:34'),
(27, 1, 'Gia đình / con cái', 'expense', '2025-11-06 08:45:34'),
(28, 1, 'Du lịch', 'expense', '2025-11-06 08:45:34'),
(29, 1, 'Tiết kiệm / đầu tư', 'expense', '2025-11-06 08:45:34'),
(30, 1, 'Khác', 'expense', '2025-11-06 08:45:34'),
(31, 1, 'Ăn uống', 'expense', '2025-11-06 08:45:34'),
(32, 1, 'Đi lại', 'expense', '2025-11-06 08:45:34'),
(33, 1, 'Mua sắm', 'expense', '2025-11-06 08:45:34'),
(34, 1, 'Tiền điện, nước, internet', 'expense', '2025-11-06 08:45:34'),
(35, 1, 'Thuê nhà', 'expense', '2025-11-06 08:45:34'),
(36, 1, 'Học tập / giáo dục', 'expense', '2025-11-06 08:45:34'),
(37, 1, 'Giải trí', 'expense', '2025-11-06 08:45:34'),
(38, 1, 'Sức khỏe / y tế', 'expense', '2025-11-06 08:45:34'),
(39, 1, 'Gia đình / con cái', 'expense', '2025-11-06 08:45:34'),
(40, 1, 'Du lịch', 'expense', '2025-11-06 08:45:34'),
(41, 1, 'Tiết kiệm / đầu tư', 'expense', '2025-11-06 08:45:34'),
(42, 1, 'Khác', 'expense', '2025-11-06 08:45:34'),
(43, 1, 'ăn', 'income', '2025-11-20 16:43:53'),
(44, 4, 'anh', 'income', '2025-11-20 18:10:04'),
(45, 1, 'ăn', 'expense', '2025-11-20 18:53:04'),
(46, 2, 'ăn', 'expense', '2025-11-21 03:22:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `expenses`
--

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `category_id`, `title`, `amount`, `note`, `date`, `created_at`) VALUES
(3, 1, 21, 'Mua sắm', 9000000.00, 'tiền an', '2025-11-20', '2025-11-20 04:35:15'),
(2, 2, NULL, 'mua nhà ', 1230000001.00, 'mua nhà', '2025-11-04', '2025-11-04 22:47:23'),
(4, 1, 22, 'Tiền điện, nước, internet', 3000000.00, '435', '2025-11-19', '2025-11-20 05:10:21'),
(5, 1, 28, 'Du lịch', 123000000.00, 'dl', '2025-11-20', '2025-11-20 18:28:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `incomes`
--

DROP TABLE IF EXISTS `incomes`;
CREATE TABLE IF NOT EXISTS `incomes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `incomes`
--

INSERT INTO `incomes` (`id`, `user_id`, `category_id`, `title`, `amount`, `note`, `date`, `created_at`) VALUES
(5, 1, 1, 'Lương chính', 345678908.00, 'công ty anh dũng', '2025-11-06', '2025-11-06 09:13:08'),
(2, 2, NULL, 'bán nhà', 123000000.00, 'bán nhà ', '2025-11-04', '2025-11-04 22:46:34'),
(3, 2, NULL, 'Lương tháng 10', 1800000000.00, 'lương', '2025-11-04', '2025-11-04 23:19:44'),
(4, 2, NULL, 'lương', 123123423423.00, 'sfdfsf', '2025-11-06', '2025-11-06 06:23:10'),
(6, 1, 1, 'Lương chính', 123445.00, 'luong', '2025-11-20', '2025-11-20 04:30:51'),
(7, 1, 5, 'Bán hàng', 2000.00, 'tip', '2025-11-20', '2025-11-20 04:31:13'),
(8, 1, 3, 'Làm thêm giờ', 23000.00, 'thêm', '2025-11-20', '2025-11-20 04:31:25'),
(9, 1, 14, 'Bán hàng', 100000000.00, 'bán ', '2025-11-20', '2025-11-20 05:15:43'),
(10, 1, 7, 'Cổ tức / đầu tư', 43340.00, 'đầu tư ', '2025-11-20', '2025-11-20 19:40:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `phone`, `created_at`, `role`) VALUES
(1, 'dung', '$2y$10$biPnemGCcInTu8LPFgWLI.Rr7d8YnE3fBQf8wZClvB2CQFw2vzUCS', 'Giàng A Dụng', '09876543456', '2025-11-04 22:32:01', 'user'),
(2, 'lethiendung', '$2y$10$5.ZPRiiG6wIKWTV.esMOXu8/EYQjrfjCXXSXeRr0MHbjb5hWe41NO', 'Lê Thiên Dũng', '09857437535', '2025-11-04 22:45:12', 'user'),
(4, 'admin', '$2y$10$aj3WDkX1I8pQkZVCzdOFMuozznhXlrVNTP/KyeTGo.W7vRp.tsUKi', 'Quản trị viên', '4048578374', '2025-11-06 06:50:13', 'admin'),
(5, 'dug12', '$2y$10$Y3VbArM/C89h5HU/1CfIxeH7tPJcEND4hZXG2dTL52e9cNHVPGUru', 'sdfsdf', '0987654', '2025-11-21 03:13:30', 'user'),
(6, 'admin1', '$2y$10$R/diX6RNyIiQhUxIFrmkAeGBsaJIGIR3UQ3LNwJHwv49YB0fzecL.', 'admin', NULL, '2025-11-21 03:48:04', 'admin'),
(7, 'dung4', '$2y$10$DNzUcVXTwFmJ/5hKUAHyaeSCLmYfGisbmf4OSFgIQ5vvURAo2cbeu', 'dung', '987645323', '2025-11-21 04:43:00', 'user');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

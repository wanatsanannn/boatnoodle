-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 07:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `noodle_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `sort_order`, `status`, `created_at`) VALUES
(1, 'ก๋วยเตี๋ยวน้ำตก', 1, 'active', '2026-02-26 18:05:22'),
(2, 'เครื่องเคียง', 4, 'active', '2026-02-26 18:05:22'),
(3, 'เครื่องดื่ม', 5, 'active', '2026-02-26 18:05:22'),
(4, 'ของหวาน', 6, 'active', '2026-02-26 18:05:22'),
(5, 'ข้าว', 2, 'active', '2026-02-26 19:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_normal` decimal(8,2) NOT NULL DEFAULT 0.00,
  `price_special` decimal(8,2) DEFAULT NULL,
  `has_spice_option` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('available','sold_out') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `image`, `price_normal`, `price_special`, `has_spice_option`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'ก๋วยเตี๋ยวหมู', 'หมูชิ้น ตับ', 'img_69a0b6d86a648.jpg', 20.00, 40.00, 1, 'available', '2026-02-26 18:05:22', '2026-03-01 00:57:28'),
(6, 2, 'ลูกชิ้นปิ้ง', 'ลูกชิ้นปิ้ง เสิร์ฟพร้อมน้ำจิ้ม', 'img_69a17a0c7a86f.jpg', 10.00, NULL, 0, 'available', '2026-02-26 18:05:22', '2026-02-27 11:03:40'),
(7, 2, 'กากเจียว', 'กากหมูเจียว', 'img_69a1a51c1385c.jpg', 25.00, NULL, 0, 'available', '2026-02-26 18:05:22', '2026-02-27 14:07:24'),
(9, 3, 'น้ำเปล่า', 'น้ำดื่ม', 'img_69a1a569589ab.jpg', 10.00, NULL, 0, 'available', '2026-02-26 18:05:22', '2026-02-27 14:08:41'),
(10, 3, 'น้ำกระเจี๊ยบ', NULL, 'img_69a1b313b1566.jpg', 25.00, NULL, 0, 'available', '2026-02-26 18:05:22', '2026-02-27 15:06:59'),
(11, 3, 'น้ำโอเลี้ยง', NULL, 'img_69a1b3905958f.jpg', 25.00, NULL, 0, 'available', '2026-02-26 18:05:22', '2026-02-27 15:09:04'),
(15, 3, 'ชาดำเย็น', NULL, 'img_69a1b2f0374ad.jpg', 25.00, NULL, 0, 'available', '2026-02-26 19:14:00', '2026-02-27 15:06:24'),
(16, 3, 'น้ำเก๊กฮวย', NULL, 'img_69a1b334ce12c.jpg', 25.00, NULL, 0, 'available', '2026-02-26 19:14:54', '2026-02-27 15:07:32'),
(17, 3, 'น้ำลำไย', NULL, 'img_69a1be4a28fe7.jpg', 25.00, NULL, 0, 'available', '2026-02-26 19:15:09', '2026-02-27 15:54:50'),
(18, 3, 'น้ำฝรั่ง', NULL, 'img_69a1be18da2fb.jpg', 25.00, NULL, 0, 'available', '2026-02-26 19:15:34', '2026-02-27 15:54:00'),
(20, 3, 'น้ำส้มคั้น', NULL, 'img_69a1be6284cfb.jpg', 25.00, NULL, 0, 'available', '2026-02-26 19:16:49', '2026-02-27 15:55:14'),
(22, 3, 'น้ำอัดลม', NULL, 'img_69a1b323dc9ad.jpg', 15.00, NULL, 0, 'available', '2026-02-26 19:17:45', '2026-02-27 15:07:15'),
(31, 5, 'ข้าวกระเพราถาด', NULL, 'img_69a0bcafed745.jpg', 60.00, NULL, 0, 'available', '2026-02-26 21:35:43', '2026-02-27 16:38:44'),
(32, 2, 'ไข่ต้ม', NULL, 'img_69a1a53bb7acc.jpg', 10.00, NULL, 0, 'available', '2026-02-26 21:51:46', '2026-02-27 14:07:55'),
(33, 2, 'ไข่ดาว', NULL, 'img_69a1a530b08ab.jpg', 10.00, NULL, 1, 'available', '2026-02-26 21:51:58', '2026-02-27 14:07:44'),
(34, 2, 'ไข่เจียว', NULL, 'img_69a1a548eb432.jpg', 10.00, NULL, 1, 'available', '2026-02-26 21:52:11', '2026-02-27 14:08:08'),
(35, 4, 'โมจิครีมชีส', NULL, 'img_69a1a8c768361.jpg', 40.00, NULL, 0, 'available', '2026-02-27 14:23:03', '2026-02-27 14:23:03'),
(36, 3, 'น้ำแข็งเปล่า', NULL, 'img_69a1b44276eb5.jpg', 2.00, NULL, 0, 'available', '2026-02-27 15:12:02', '2026-02-27 15:12:02'),
(37, 1, 'ก๋วยเตี๋ยวเนื้อ', NULL, 'img_69a1b92f56b48.jpg', 40.00, NULL, 1, 'available', '2026-02-27 15:33:03', '2026-02-27 15:33:03'),
(39, 5, 'ข้าวขาหมูเนื้อหนัง', NULL, 'img_69a563da4b27e.jpg', 50.00, 60.00, 0, 'available', '2026-03-02 10:18:02', '2026-03-02 10:18:02'),
(40, 5, 'ข้าวขาหมูเนื้อหนังไส้', NULL, 'img_69a56471e9ca5.jpg', 60.00, NULL, 0, 'available', '2026-03-02 10:20:33', '2026-03-02 10:20:33'),
(41, 5, 'ข้าวขาหมูเนื้อหนังคากิ', NULL, 'img_69a564843d948.jpg', 60.00, NULL, 0, 'available', '2026-03-02 10:20:52', '2026-03-02 10:20:52'),
(42, 5, 'ต้มเลือดหมู', NULL, 'img_69a564dd25033.jpg', 50.00, NULL, 0, 'available', '2026-03-02 10:22:21', '2026-03-02 10:22:21'),
(43, 5, 'กับข้าวขาหมู', 'กับข้าว', 'img_69a5650acf38a.jpg', 100.00, NULL, 0, 'available', '2026-03-02 10:23:06', '2026-03-02 10:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_option_groups`
--

CREATE TABLE `menu_item_option_groups` (
  `menu_item_id` int(11) NOT NULL,
  `option_group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_item_option_groups`
--

INSERT INTO `menu_item_option_groups` (`menu_item_id`, `option_group_id`) VALUES
(1, 1),
(1, 2),
(6, 4),
(22, 3),
(31, 7),
(31, 8),
(37, 1),
(37, 2),
(39, 9),
(40, 9),
(41, 9);

-- --------------------------------------------------------

--
-- Table structure for table `option_choices`
--

CREATE TABLE `option_choices` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `extra_price` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาเพิ่ม (0 = ไม่คิดเพิ่ม)',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'เลือกเป็นค่าเริ่มต้น',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `option_choices`
--

INSERT INTO `option_choices` (`id`, `group_id`, `name`, `extra_price`, `is_default`, `sort_order`, `status`, `created_at`) VALUES
(1, 1, 'เส้นเล็ก', 0.00, 1, 1, 'active', '2026-02-26 18:05:22'),
(2, 1, 'เส้นใหญ่', 0.00, 0, 2, 'active', '2026-02-26 18:05:22'),
(3, 1, 'หมี่ขาว', 0.00, 0, 3, 'active', '2026-02-26 18:05:22'),
(4, 1, 'วุ้นเส้น', 0.00, 0, 4, 'active', '2026-02-26 18:05:22'),
(5, 1, 'บะหมี่', 0.00, 0, 5, 'active', '2026-02-26 18:05:22'),
(6, 2, 'เพิ่มหมู', 10.00, 0, 1, 'active', '2026-02-26 18:05:22'),
(8, 2, 'เพิ่มลูกชิ้น', 10.00, 0, 3, 'active', '2026-02-26 18:05:22'),
(9, 2, 'เพิ่มผัก', 5.00, 0, 4, 'active', '2026-02-26 18:05:22'),
(10, 3, 'โค้ก', 0.00, 0, 1, 'active', '2026-02-26 19:33:34'),
(11, 3, 'ส้ม', 0.00, 0, 2, 'active', '2026-02-26 19:34:01'),
(12, 3, 'เขียว', 0.00, 0, 3, 'active', '2026-02-26 19:34:15'),
(13, 3, 'แดง', 0.00, 0, 4, 'active', '2026-02-26 19:34:24'),
(14, 3, 'สไปร์', 0.00, 0, 5, 'active', '2026-02-26 19:34:42'),
(15, 1, 'มาม่า', 0.00, 0, 6, 'active', '2026-02-26 19:42:35'),
(16, 4, 'หมูล้วน', 0.00, 0, 1, 'active', '2026-02-26 21:13:06'),
(17, 4, 'เอ็นหมู', 0.00, 0, 2, 'active', '2026-02-26 21:13:17'),
(18, 4, 'เอ็นเนื้อ', 0.00, 0, 3, 'active', '2026-02-26 21:13:35'),
(25, 1, 'เกาเหลา', 0.00, 0, 7, 'active', '2026-02-26 21:23:10'),
(26, 7, 'เนื้อสับ', 0.00, 0, 1, 'active', '2026-02-26 21:36:09'),
(27, 7, 'เนื้อชิ้น', 0.00, 0, 2, 'active', '2026-02-26 21:36:18'),
(28, 7, 'เนื้อเปื่อย', 0.00, 0, 3, 'active', '2026-02-26 21:36:27'),
(29, 7, 'หมูสับ', 0.00, 0, 4, 'active', '2026-02-26 21:36:35'),
(30, 7, 'หมูชิ้น', 0.00, 0, 5, 'active', '2026-02-26 21:36:45'),
(31, 7, 'หมูเปื่อย', 0.00, 0, 6, 'active', '2026-02-26 21:36:58'),
(32, 7, 'หมูกรอบ', 0.00, 0, 7, 'active', '2026-02-26 21:37:10'),
(33, 7, 'หมูสับ + ไข่เยี่ยวม้า', 15.00, 0, 8, 'active', '2026-02-26 21:37:44'),
(34, 8, 'ไข่ดาว', 10.00, 0, 0, 'active', '2026-02-26 21:39:47'),
(35, 8, 'ไข่เจียว', 10.00, 0, 0, 'active', '2026-02-26 21:39:57'),
(36, 9, 'ไข่ต้ม', 10.00, 0, 1, 'active', '2026-02-26 21:44:10');

-- --------------------------------------------------------

--
-- Table structure for table `option_groups`
--

CREATE TABLE `option_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('single','multiple') NOT NULL DEFAULT 'single' COMMENT 'single=เลือกได้ 1, multiple=เลือกได้หลายอย่าง',
  `required` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'ต้องเลือกหรือไม่',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `option_groups`
--

INSERT INTO `option_groups` (`id`, `name`, `type`, `required`, `sort_order`, `status`, `created_at`) VALUES
(1, 'ชนิดเส้น', 'single', 1, 1, 'active', '2026-02-26 18:05:22'),
(2, 'ท็อปปิ้งเพิ่ม', 'multiple', 0, 2, 'active', '2026-02-26 18:05:22'),
(3, 'น้ำอัดลม', 'single', 0, 7, 'active', '2026-02-26 19:33:20'),
(4, 'ลูกชิ้นปิ้ง', 'single', 1, 6, 'active', '2026-02-26 21:12:47'),
(7, 'กระเพราถาด', 'single', 1, 3, 'active', '2026-02-26 21:35:56'),
(8, 'ไข่', 'multiple', 0, 4, 'active', '2026-02-26 21:38:39'),
(9, 'ไข่ต้ม', 'multiple', 0, 5, 'active', '2026-02-26 21:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `status` enum('pending','cooking','ready','served','completed','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `table_id`, `order_number`, `status`, `total_amount`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, '0001', 'completed', 45.00, NULL, '2026-02-26 18:19:51', '2026-02-26 18:22:53'),
(2, 1, '0002', 'completed', 50.00, NULL, '2026-02-26 18:21:42', '2026-02-26 18:22:53'),
(3, 5, '0003', 'completed', 37.00, NULL, '2026-02-27 08:33:53', '2026-02-27 09:27:34'),
(4, 5, '0004', 'completed', 40.00, NULL, '2026-02-27 08:39:38', '2026-02-27 09:27:34'),
(5, 5, '0005', 'completed', 50.00, NULL, '2026-02-27 08:41:18', '2026-02-27 09:27:34'),
(6, 8, '0006', 'completed', 60.00, NULL, '2026-02-27 08:43:06', '2026-02-27 09:27:40'),
(7, 11, '0007', 'completed', 60.00, NULL, '2026-02-27 09:28:16', '2026-02-27 10:09:56'),
(8, 5, '0008', 'completed', 20.00, NULL, '2026-02-27 09:29:04', '2026-02-27 10:09:50'),
(9, 1, '0009', 'completed', 222.00, NULL, '2026-02-27 15:19:45', '2026-02-27 15:20:50'),
(10, 5, '0010', 'completed', 170.00, NULL, '2026-02-27 15:24:39', '2026-02-27 15:42:33'),
(11, 5, '0011', 'completed', 55.00, NULL, '2026-02-27 15:27:07', '2026-02-27 15:42:33'),
(12, 5, '0012', 'completed', 25.00, NULL, '2026-02-27 15:57:42', '2026-02-27 15:58:40'),
(13, 5, '0013', 'completed', 100.00, NULL, '2026-02-28 20:06:33', '2026-02-28 20:19:25'),
(14, 5, '0014', 'completed', 100.00, NULL, '2026-02-28 20:09:47', '2026-02-28 20:19:25'),
(15, 5, '0015', 'completed', 10.00, NULL, '2026-02-28 20:16:03', '2026-02-28 20:19:25'),
(16, 1, '0016', 'completed', 25.00, NULL, '2026-02-28 20:28:00', '2026-02-28 20:29:43'),
(17, 1, '0017', 'completed', 25.00, NULL, '2026-02-28 20:47:29', '2026-02-28 20:48:10'),
(18, 1, '0018', 'completed', 70.00, NULL, '2026-03-01 01:04:01', '2026-03-01 01:05:48'),
(23, 1, '0023', 'completed', 20.00, NULL, '2026-03-01 04:30:55', '2026-03-01 04:32:28'),
(24, 1, '0024', 'completed', 60.00, NULL, '2026-03-01 04:43:10', '2026-03-01 04:45:36'),
(25, 1, '0025', 'completed', 60.00, NULL, '2026-03-02 13:40:30', '2026-03-02 14:09:49'),
(26, 1, '0026', 'completed', 10.00, NULL, '2026-03-02 14:10:15', '2026-03-02 14:15:54'),
(27, 1, '0027', 'cancelled', 40.00, NULL, '2026-03-02 14:48:58', '2026-03-02 14:49:17'),
(28, 1, '0028', 'completed', 25.00, NULL, '2026-03-02 15:22:51', '2026-03-02 15:58:33'),
(29, 1, '0029', 'cancelled', 25.00, NULL, '2026-03-02 16:24:50', '2026-03-02 16:25:00'),
(30, 1, '0030', 'cancelled', 110.00, NULL, '2026-03-02 17:15:16', '2026-03-02 17:16:26'),
(31, 1, '0031', 'cancelled', 100.00, NULL, '2026-03-02 17:20:21', '2026-03-02 17:20:24'),
(32, 1, '0032', 'cancelled', 100.00, NULL, '2026-03-02 17:43:25', '2026-03-02 17:43:28');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` enum('normal','special') NOT NULL DEFAULT 'normal',
  `spice_level` tinyint(4) NOT NULL DEFAULT 0,
  `unit_price` decimal(8,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `status` enum('pending','cooking','ready','served') NOT NULL DEFAULT 'pending',
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `size`, `spice_level`, `unit_price`, `subtotal`, `status`, `note`, `created_at`) VALUES
(3, 3, 1, 1, 'normal', 0, 20.00, 20.00, 'served', '', '2026-02-27 08:33:53'),
(4, 3, 22, 1, 'normal', 0, 15.00, 15.00, 'served', '', '2026-02-27 08:33:53'),
(6, 4, 1, 1, 'special', 0, 40.00, 40.00, 'served', '', '2026-02-27 08:39:38'),
(9, 7, 31, 1, 'normal', 0, 60.00, 60.00, 'served', '', '2026-02-27 09:28:16'),
(10, 8, 1, 1, 'normal', 0, 20.00, 20.00, 'served', '', '2026-02-27 09:29:04'),
(11, 9, 1, 1, 'normal', 2, 30.00, 30.00, 'served', '', '2026-02-27 15:19:45'),
(12, 9, 31, 2, 'normal', 0, 70.00, 140.00, 'served', '', '2026-02-27 15:19:45'),
(13, 9, 36, 1, 'normal', 0, 2.00, 2.00, 'served', '', '2026-02-27 15:19:45'),
(14, 9, 9, 1, 'normal', 0, 10.00, 10.00, 'served', '', '2026-02-27 15:19:45'),
(15, 9, 35, 1, 'normal', 0, 40.00, 40.00, 'served', '', '2026-02-27 15:19:45'),
(16, 10, 1, 1, 'normal', 0, 30.00, 30.00, 'served', '', '2026-02-27 15:24:39'),
(17, 10, 35, 1, 'normal', 0, 40.00, 40.00, 'served', '', '2026-02-27 15:24:39'),
(18, 10, 18, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:24:39'),
(19, 10, 10, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:24:39'),
(20, 10, 15, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:24:39'),
(21, 10, 17, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:24:39'),
(22, 11, 1, 1, 'normal', 0, 30.00, 30.00, 'served', '', '2026-02-27 15:27:07'),
(23, 11, 15, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:27:07'),
(24, 12, 15, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-27 15:57:42'),
(27, 15, 6, 1, 'normal', 0, 10.00, 10.00, 'served', '', '2026-02-28 20:16:03'),
(28, 16, 15, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-02-28 20:28:00'),
(29, 17, 7, 1, 'normal', 0, 25.00, 25.00, 'ready', '', '2026-02-28 20:47:29'),
(30, 18, 31, 1, 'normal', 0, 70.00, 70.00, 'served', '', '2026-03-01 01:04:01'),
(37, 24, 31, 1, 'normal', 0, 60.00, 60.00, 'served', '', '2026-03-01 04:43:10'),
(38, 25, 31, 1, 'normal', 0, 60.00, 60.00, 'served', '', '2026-03-02 13:40:30'),
(39, 26, 33, 1, 'normal', 0, 10.00, 10.00, 'served', '', '2026-03-02 14:10:15'),
(40, 27, 37, 1, 'normal', 0, 40.00, 40.00, '', '', '2026-03-02 14:48:58'),
(41, 28, 10, 1, 'normal', 0, 25.00, 25.00, 'served', '', '2026-03-02 15:22:51'),
(42, 29, 17, 1, 'normal', 0, 25.00, 25.00, '', '', '2026-03-02 16:24:50'),
(43, 30, 43, 1, 'normal', 0, 100.00, 100.00, '', '', '2026-03-02 17:15:16'),
(44, 30, 6, 1, 'normal', 0, 10.00, 10.00, '', '', '2026-03-02 17:15:16'),
(45, 31, 43, 1, 'normal', 0, 100.00, 100.00, '', '', '2026-03-02 17:20:21'),
(46, 32, 37, 1, 'normal', 1, 40.00, 40.00, '', '', '2026-03-02 17:43:25'),
(47, 32, 31, 1, 'normal', 0, 60.00, 60.00, '', '', '2026-03-02 17:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_options`
--

CREATE TABLE `order_item_options` (
  `id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `option_choice_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL COMMENT 'ชื่อตัวเลือก ณ เวลาสั่ง',
  `extra_price` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาเพิ่ม ณ เวลาสั่ง',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item_options`
--

INSERT INTO `order_item_options` (`id`, `order_item_id`, `option_choice_id`, `option_name`, `extra_price`, `created_at`) VALUES
(3, 3, 1, 'เส้นเล็ก', 0.00, '2026-02-27 08:33:53'),
(4, 4, 10, 'โค้ก', 0.00, '2026-02-27 08:33:53'),
(5, 6, 15, 'มาม่า', 0.00, '2026-02-27 08:39:38'),
(7, 9, 31, 'หมูเปื่อย', 0.00, '2026-02-27 09:28:16'),
(8, 10, 2, 'เส้นใหญ่', 0.00, '2026-02-27 09:29:04'),
(9, 11, 1, 'เส้นเล็ก', 0.00, '2026-02-27 15:19:45'),
(10, 11, 6, 'เพิ่มหมู', 10.00, '2026-02-27 15:19:45'),
(11, 12, 27, 'เนื้อชิ้น', 0.00, '2026-02-27 15:19:45'),
(12, 12, 34, 'ไข่ดาว', 10.00, '2026-02-27 15:19:45'),
(13, 16, 2, 'เส้นใหญ่', 0.00, '2026-02-27 15:24:39'),
(14, 16, 6, 'เพิ่มหมู', 10.00, '2026-02-27 15:24:39'),
(15, 22, 2, 'เส้นใหญ่', 0.00, '2026-02-27 15:27:07'),
(16, 22, 8, 'เพิ่มลูกชิ้น', 10.00, '2026-02-27 15:27:07'),
(17, 27, 16, 'หมูล้วน', 0.00, '2026-02-28 20:16:03'),
(18, 30, 26, 'เนื้อสับ', 0.00, '2026-03-01 01:04:01'),
(19, 30, 34, 'ไข่ดาว', 10.00, '2026-03-01 01:04:01'),
(26, 37, 27, 'เนื้อชิ้น', 0.00, '2026-03-01 04:43:10'),
(27, 38, 27, 'เนื้อชิ้น', 0.00, '2026-03-02 13:40:30'),
(28, 40, 1, 'เส้นเล็ก', 0.00, '2026-03-02 14:48:58'),
(29, 44, 18, 'เอ็นเนื้อ', 0.00, '2026-03-02 17:15:16'),
(30, 46, 1, 'เส้นเล็ก', 0.00, '2026-03-02 17:43:25'),
(31, 47, 28, 'เนื้อเปื่อย', 0.00, '2026-03-02 17:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `method` enum('cash','promptpay') NOT NULL DEFAULT 'cash',
  `received_by` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `table_id`, `total_amount`, `method`, `received_by`, `note`, `created_at`) VALUES
(1, 1, 95.00, 'cash', 5, NULL, '2026-02-26 18:22:53'),
(2, 5, 127.00, 'cash', 5, NULL, '2026-02-27 09:27:34'),
(3, 8, 60.00, 'promptpay', 5, NULL, '2026-02-27 09:27:40'),
(4, 5, 20.00, 'cash', 5, NULL, '2026-02-27 10:09:50'),
(5, 11, 60.00, 'promptpay', 5, NULL, '2026-02-27 10:09:56'),
(6, 1, 222.00, 'cash', 5, NULL, '2026-02-27 15:20:50'),
(7, 5, 225.00, 'promptpay', 5, NULL, '2026-02-27 15:42:33'),
(8, 5, 25.00, 'promptpay', 5, NULL, '2026-02-27 15:58:40'),
(9, 5, 210.00, 'cash', 5, NULL, '2026-02-28 20:19:25'),
(10, 1, 25.00, 'promptpay', 5, NULL, '2026-02-28 20:29:43'),
(11, 1, 25.00, 'cash', 5, NULL, '2026-02-28 20:48:10'),
(12, 1, 70.00, 'promptpay', 5, NULL, '2026-03-01 01:05:48'),
(14, 1, 20.00, 'promptpay', 5, NULL, '2026-03-01 04:32:28'),
(15, 1, 60.00, 'promptpay', 5, NULL, '2026-03-01 04:45:36'),
(16, 1, 60.00, 'cash', 5, NULL, '2026-03-02 14:09:49'),
(17, 1, 10.00, 'cash', 5, NULL, '2026-03-02 14:15:54'),
(18, 1, 25.00, 'promptpay', 5, NULL, '2026-03-02 15:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `seats` int(11) NOT NULL DEFAULT 4,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('available','occupied') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `table_number`, `seats`, `qr_code`, `status`, `created_at`) VALUES
(1, '1', 4, NULL, 'occupied', '2026-02-26 18:05:22'),
(2, '2', 4, NULL, 'available', '2026-02-26 18:05:22'),
(3, '3', 4, NULL, 'available', '2026-02-26 18:05:22'),
(4, '4', 4, NULL, 'available', '2026-02-26 18:05:22'),
(5, '5', 4, NULL, 'available', '2026-02-26 18:05:22'),
(6, '6', 4, NULL, 'available', '2026-02-26 18:05:22'),
(7, '7', 4, NULL, 'available', '2026-02-26 18:05:22'),
(8, '8', 4, NULL, 'available', '2026-02-26 18:05:22'),
(9, '9', 4, NULL, 'available', '2026-02-26 18:05:22'),
(10, '10', 4, NULL, 'available', '2026-02-26 18:05:22'),
(11, '11', 4, NULL, 'available', '2026-02-26 18:05:22'),
(12, '12', 4, NULL, 'available', '2026-02-26 18:05:22'),
(13, '13', 4, NULL, 'available', '2026-02-26 18:05:22'),
(14, '14', 4, NULL, 'available', '2026-02-26 18:05:22'),
(15, '15', 2, NULL, 'available', '2026-02-26 18:05:22'),
(16, '16', 2, NULL, 'available', '2026-02-26 18:05:22'),
(17, '17', 2, NULL, 'available', '2026-02-26 18:05:22'),
(19, '19', 2, NULL, 'available', '2026-02-26 18:05:22'),
(20, '20', 2, NULL, 'available', '2026-02-26 18:05:22'),
(21, '21', 2, NULL, 'available', '2026-02-26 18:05:22'),
(22, '22', 6, NULL, 'available', '2026-02-26 18:05:22'),
(23, '23', 6, NULL, 'available', '2026-02-26 18:05:22'),
(24, '24', 6, NULL, 'available', '2026-02-26 18:05:22'),
(25, '25', 6, NULL, 'available', '2026-02-26 18:05:22'),
(26, '26', 8, NULL, 'available', '2026-02-26 18:05:22'),
(27, '27', 8, NULL, 'available', '2026-02-26 18:05:22'),
(28, '28', 8, NULL, 'available', '2026-02-26 18:05:22'),
(32, '18', 4, NULL, 'available', '2026-03-01 04:19:45'),
(33, '30', 4, NULL, 'available', '2026-03-01 04:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','manager','chef','waiter','cashier') NOT NULL DEFAULT 'waiter',
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `phone`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$dtbG7ePVGeHzyLRrbYBnIuyIQm6TmsicSewg/W5sJg8uNK.UNHSQ2', 'วนัสนันท์', 'admin', '0993849577', 'active', '2026-02-26 18:05:22'),
(2, 'manager', '$2y$10$29apD/08EzcmrQqA/sLWpeVY6U.WFitqQ3PiWnAPl/fjbXPMpVuEG', 'หัวหน้างาน', 'manager', NULL, 'active', '2026-02-26 18:05:22'),
(3, 'chef', '$2y$10$SSg70ioVYuW3d5.IdfdDCea7xNsxod6FStzct940Ny1ZHft7j54C.', 'พ่อครัว', 'chef', NULL, 'active', '2026-02-26 18:05:22'),
(4, 'waiter', '$2y$10$FvzEeGfmH.i7HHRPuX.rg.HVWGjlm5mwqbmuxajthZaZxpiFQMvFm', 'พนักงานเสิร์ฟ', 'waiter', NULL, 'active', '2026-02-26 18:05:22'),
(5, 'cashier', '$2y$10$MDsOFLLlWT8b9O3G7iYrYeEGhWZfvEOHeVucE2wjQ3zlmqf7QULPK', 'แคชเชียร์', 'cashier', NULL, 'active', '2026-02-26 18:05:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `menu_item_option_groups`
--
ALTER TABLE `menu_item_option_groups`
  ADD PRIMARY KEY (`menu_item_id`,`option_group_id`),
  ADD KEY `option_group_id` (`option_group_id`);

--
-- Indexes for table `option_choices`
--
ALTER TABLE `option_choices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `option_groups`
--
ALTER TABLE `option_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `order_item_options`
--
ALTER TABLE `order_item_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `option_choice_id` (`option_choice_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `option_choices`
--
ALTER TABLE `option_choices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `option_groups`
--
ALTER TABLE `option_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `order_item_options`
--
ALTER TABLE `order_item_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item_option_groups`
--
ALTER TABLE `menu_item_option_groups`
  ADD CONSTRAINT `menu_item_option_groups_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_item_option_groups_ibfk_2` FOREIGN KEY (`option_group_id`) REFERENCES `option_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `option_choices`
--
ALTER TABLE `option_choices`
  ADD CONSTRAINT `option_choices_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `option_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item_options`
--
ALTER TABLE `order_item_options`
  ADD CONSTRAINT `order_item_options_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_options_ibfk_2` FOREIGN KEY (`option_choice_id`) REFERENCES `option_choices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

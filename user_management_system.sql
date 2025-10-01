-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2025 at 02:43 PM
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
-- Database: `user_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `image_filename` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `created_by`, `created_at`, `updated_at`, `is_published`, `published_at`, `expiry_date`, `priority`, `image_filename`, `image_path`) VALUES
('68d1447be1c09', 'Telcom anniversary ', '40th year', 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-22 12:43:39', '2025-09-22 12:43:39', 1, '2025-09-22 12:43:39', NULL, 'medium', '68d1447be1a8e_1758545019.png', 'uploads/announcements/68d1447be1a8e_1758545019.png'),
('68d42305a8c24', 'Service Upgrade', 'Tunisie Telecom is proud to announce a major upgrade to our fiber-optic network, delivering even faster and more reliable internet speeds to households across Tunisia.', 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-24 16:57:41', '2025-09-24 16:57:41', 1, '2025-09-24 16:57:41', NULL, 'medium', '68d42305a8afe_1758733061.webp', 'uploads/announcements/68d42305a8afe_1758733061.webp'),
('68d4234b2503c', 'New Package Launch', 'Enjoy unlimited calls, ultra-fast internet, and exclusive roaming benefits with our new TT Max+ package. Available now at all Tunisie Telecom branches', 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-24 16:58:51', '2025-09-24 16:58:51', 1, '2025-09-24 16:58:51', NULL, 'medium', '68d4234b24f22_1758733131.jpeg', 'uploads/announcements/68d4234b24f22_1758733131.jpeg'),
('68d423b1ece72', 'Seasonal Promotion', 'Stay connected this school season with our discounted student bundles. Fast internet, affordable rates, and special offers just for students', 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-24 17:00:33', '2025-09-24 17:00:33', 1, '2025-09-24 17:00:33', NULL, 'medium', '68d423b1ecd5c_1758733233.jpeg', 'uploads/announcements/68d423b1ecd5c_1758733233.jpeg'),
('68d462a61d8db', 'OH!!MEAGA', 'Tunisie Telecom – Oh!Mega Packages\r\nDiscover the new Oh!Mega offers from Tunisie Telecom, designed to keep you connected at the best prices. Enjoy high-speed mobile internet with flexible data packages tailored to your needs:\r\n\r\n7 GB for only 19 DT\r\n\r\n25 GB for only 29 DT\r\n\r\n40 GB for only 49 DT\r\n\r\nStay connected, browse, stream, and share without limits – all with Tunisie Telecom, your trusted partner in digital connectivity.', 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-24 21:29:10', '2025-09-24 21:29:10', 1, '2025-09-24 21:29:10', NULL, 'medium', '68d462a61d7e6_1758749350.png', 'uploads/announcements/68d462a61d7e6_1758749350.png');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `content_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `type` enum('article','news','announcement','policy','handbook','template','form','guide','other') NOT NULL,
  `category` enum('policy','handbook','template','form','guide','other') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(36) NOT NULL,
  `published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `title`, `body`, `file_name`, `file_path`, `file_size`, `image_path`, `type`, `category`, `created_date`, `created_by`, `published`, `published_at`) VALUES
('68d144f36bf44', 'pass', 'text passwords', 'pass.text', 'uploads/68d144f36bf44.text', 228, NULL, 'form', NULL, '2025-09-22 12:45:39', 'f48e7132-9284-409a-96ea-6af8d5605a1d', 1, NULL),
('68d14a2a8d72b', 'oiih', 'igiu', 'login.php', 'uploads/68d14a2a8d72b.php', 11176, NULL, 'handbook', NULL, '2025-09-22 13:07:54', 'f48e7132-9284-409a-96ea-6af8d5605a1d', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` varchar(36) NOT NULL,
  `redeem_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `partner_name` varchar(255) NOT NULL,
  `discount_rate` decimal(5,2) NOT NULL CHECK (`discount_rate` > 0 and `discount_rate` <= 100),
  `expiry_date` date NOT NULL,
  `usage_count` int(11) DEFAULT 0,
  `issued_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `redeem_code`, `description`, `coupon_code`, `partner_name`, `discount_rate`, `expiry_date`, `usage_count`, `issued_by`, `created_at`) VALUES
('xxxxx', 'xxxxx', 'bdyjfjy', 'xxxxx', 'ngfjyf', 50.00, '2025-09-29', 1, 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-26 12:32:25'),
('xxxxxxxxx', 'xxxxxxxxx', 'sdvsedcv', '', 'edcedas', 12.00, '2025-10-12', 1, 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-26 11:24:09'),
('xxxxxxxxxx', 'xxxxxxxxxx', 'yg79g08hh', '', 'espin', 33.00, '2025-10-12', 1, 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-26 11:09:58'),
('xxxxxxxxxxxxxx', 'xxxxxxxxxxxxxx', 'u6j6uum', '', 'mytk', 20.00, '2025-10-12', 0, 'f48e7132-9284-409a-96ea-6af8d5605a1d', '2025-09-26 11:10:48');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_redemptions`
--

CREATE TABLE `coupon_redemptions` (
  `redemption_id` varchar(36) NOT NULL,
  `coupon_id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_redemptions`
--

INSERT INTO `coupon_redemptions` (`redemption_id`, `coupon_id`, `employee_id`, `redeemed_at`, `coupon_code`) VALUES
('a658c647-9ac9-11f0-ad8c-7008941025b2', 'xxxxxxxxxx', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', '2025-09-26 11:12:11', ''),
('af82de8a-9ad4-11f0-ad8c-7008941025b2', 'xxxxxxxxx', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', '2025-09-26 12:31:11', 'xxxxxxxxx'),
('edbf2620-9ad4-11f0-ad8c-7008941025b2', 'xxxxx', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', '2025-09-26 12:32:55', 'xxxxx');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('suggestion','complaint','question','other') NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `employee_id`, `subject`, `message`, `category`, `status`, `created_at`, `updated_at`) VALUES
('fb_68d43680bba1f2.15153050', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', 'wronge values', 'fjlfnklknfkec', 'complaint', 'open', '2025-09-24 18:20:48', '2025-09-24 18:20:48'),
('fb_68d4684ded28b7.84701180', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', 'lhlh', 'jguob', 'other', 'open', '2025-09-24 21:53:17', '2025-09-24 21:53:17'),
('fb_68d4685b195158.60603604', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', 'kjjkubb', 'ygouvu', 'question', 'open', '2025-09-24 21:53:31', '2025-09-24 21:53:31'),
('fb_68d46881dc7150.16115330', 'e9ae41c2-8e60-41a1-9eee-f617aaafc824', 'jbjlblj', 'lblibl', 'complaint', 'open', '2025-09-24 21:54:09', '2025-09-24 21:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_responses`
--

CREATE TABLE `feedback_responses` (
  `response_id` varchar(36) NOT NULL,
  `feedback_id` varchar(36) NOT NULL,
  `admin_id` varchar(36) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_responses`
--

INSERT INTO `feedback_responses` (`response_id`, `feedback_id`, `admin_id`, `message`, `created_at`) VALUES
('resp_68d466cfb47376.62195030', 'fb_68d43680bba1f2.15153050', 'f48e7132-9284-409a-96ea-6af8d5605a1d', 'no its a true values', '2025-09-24 21:46:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('admin','employee') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `department`, `role`, `password`, `profile_picture`, `created_at`, `updated_at`) VALUES
('66352c35-4157-49d2-900f-6f9a74d9b26e', 'sajed', 'sajed@company.com', NULL, 'admin', '$2y$10$pDF55Q7yXdkIuO3wUQocKOq/q7nhHG5.9fpSae5oJTNi6oVCpUKVC', NULL, '2025-09-23 15:57:15', '2025-09-23 15:57:46'),
('e9ae41c2-8e60-41a1-9eee-f617aaafc824', 'ilyass', 'ilyasse@company.com', NULL, 'employee', '$2y$10$5vjnhcnn5wUlEGUy5ye8BeZwLbFuchYjz9ELewYzBBqhWr5OksEJq', 'uploads/profile_pictures/original/e9ae41c2-8e60-41a1-9eee-f617aaafc824_1758740929_68d441c152f4d.jpg', '2025-09-24 15:12:20', '2025-09-24 19:08:49'),
('f48e7132-9284-409a-96ea-6af8d5605a1d', 'ilyass chakroun', 'ilyass@company.com', 'develpment', 'admin', '$2y$10$h/.6jtRYxIAI9/ARFDtmO.9l1.eP/1c0OM7yGGt2Mwk1MStOztKcG', 'uploads/profile_pictures/original/f48e7132-9284-409a-96ea-6af8d5605a1d_1758726279_68d40887b7faa.png', '2025-09-22 12:38:48', '2025-09-24 15:04:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `redeem_code` (`redeem_code`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD PRIMARY KEY (`redemption_id`),
  ADD UNIQUE KEY `unique_coupon_employee` (`coupon_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `feedback_responses`
--
ALTER TABLE `feedback_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `feedback_id` (`feedback_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD CONSTRAINT `coupon_redemptions_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_redemptions_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_responses`
--
ALTER TABLE `feedback_responses`
  ADD CONSTRAINT `feedback_responses_ibfk_1` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`feedback_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_responses_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

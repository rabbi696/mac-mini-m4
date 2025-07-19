-- MySQL dump compatible with CyberPanel (MySQL 5.7/MariaDB)
-- Database: mac_software
-- Compatible with older MySQL versions

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `mac_software`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$KPgb2DD9UOL3vWJYy9KqX.rSOhyrrHGH7k45JVdvHTj7CneAxaEfC', '2025-07-19 21:19:25', '2025-07-19 21:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `software_requests`
--

CREATE TABLE `software_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `software_name` varchar(255) NOT NULL,
  `software_version` varchar(50) DEFAULT NULL,
  `additional_info` text,
  `visitor_email` varchar(150) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `software_requests`
--

INSERT INTO `software_requests` (`id`, `software_name`, `software_version`, `additional_info`, `visitor_email`, `date`, `status`, `updated_at`) VALUES
(1, 'Adobe', '3.21', 'sadas sa sad', 'golamrabby40@gmail.com', '2025-07-19 21:08:43', 'pending', '2025-07-19 21:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `donations` (if exists - for future use)
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` varchar(100) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'BDT',
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(150) DEFAULT NULL,
  `message` text,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings` (if exists - for future use)
--

CREATE TABLE `payment_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Set AUTO_INCREMENT for exported tables
--

ALTER TABLE `admin_users` AUTO_INCREMENT=2;
ALTER TABLE `contact_messages` AUTO_INCREMENT=1;
ALTER TABLE `software_requests` AUTO_INCREMENT=2;
ALTER TABLE `donations` AUTO_INCREMENT=1;
ALTER TABLE `payment_settings` AUTO_INCREMENT=1;

COMMIT;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 06:04 AM
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
-- Database: `nov_system7`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `street` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `establishment_id`, `street`, `barangay`, `municipality`, `province`, `region`) VALUES
(31, 30, 'ada', 'asd', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(32, 31, 'dsf', 'sdfs', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(33, 32, 'dsf', 'sdfs', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(34, 33, 'dsf', 'sdfs', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(35, 34, 'dsf', 'sdf', 'dsf', 'dsf', 'sad'),
(36, 35, 'dsf', 'sdf', 'dsf', 'dsf', 'Region I (Ilocos Region)'),
(37, 36, 'dsf', 'sdf', 'dsf', 'sdf', 'dsfsd'),
(38, 37, 'dsf', 'sdf', 'dsf', 'sdf', 'dsfsd'),
(39, 38, 'dsf', 'sdf', 'dsf', 'sdf', 'dsfsd'),
(40, 39, 'dsf', 'sdf', 'dsf', 'sdf', 'dsfsd'),
(41, 40, 'dsaf', 'adf', 'asf', 'sdfasdf', 'dfsfs'),
(42, 41, 'ada', 'Salvacion', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(43, 42, 'dsf', 'adas', 'Bauang', 'Ilocos Sur', 'Region I (Ilocos Region)'),
(44, 43, 'dsf', 'dfs', 'Aringay', 'La Union', 'Region I (Ilocos Region)'),
(45, 44, 'sdf', 'sdf', 'Aringay', 'asd', 'sdf'),
(46, 45, 'dfs', 'adas', 'sasd', 'asdasas', 'sdf');

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `establishment_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `owner_representative` varchar(255) DEFAULT NULL,
  `nature` varchar(255) NOT NULL,
  `products` text NOT NULL,
  `violations` text NOT NULL,
  `notice_status` varchar(50) DEFAULT NULL,
  `remarks` text NOT NULL,
  `issued_datetime` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `num_violations` varchar(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`establishment_id`, `name`, `owner_representative`, `nature`, `products`, `violations`, `notice_status`, `remarks`, `issued_datetime`, `expiry_date`, `num_violations`, `date_created`, `date_updated`) VALUES
(30, 'anonymous', 'saadsd', 'Retail', 'dsfsdfs', '[\"No PS/ICC Mark\"]', 'Received', '', '2025-05-16 09:57:11', '2025-05-20 09:57:11', '1', '2025-05-15 09:57:11', '2025-05-15 09:57:11'),
(31, 'scp', 'alto cleff', 'Retail', 'dsfsd', '[\"Invalid/suspended/cancelled BPS license\",\"Operating without Business Name Registration\",\"dsf\"]', 'Received', '', '2025-05-16 13:27:18', '2025-05-20 13:27:18', '3', '2025-05-15 13:27:18', '2025-05-15 13:27:18'),
(32, 'scp', 'alto cleff', 'Retail', 'dsfsd', '[\"Invalid/suspended/cancelled BPS license\",\"Operating without Business Name Registration\",\"dsf\"]', 'Received', '', '2025-05-16 13:27:23', '2025-05-20 13:27:23', '3', '2025-05-15 13:27:23', '2025-05-15 13:27:23'),
(33, 'scp', 'alto cleff', 'Retail', 'dsfsd', '[\"Invalid/suspended/cancelled BPS license\",\"Operating without Business Name Registration\",\"dsf\"]', 'Responded', '', '2025-05-16 13:33:27', '2025-05-20 13:33:27', '3', '2025-05-15 13:33:27', '2025-05-15 13:33:27'),
(34, 'anonymous', 'saadsd', 'Retail', 'dsfsd', '[\"No PS/ICC Mark\",\"Operating without Business Name Registration\"]', 'Responded', 'dsf', '2025-05-16 13:38:26', '2025-05-20 13:38:26', '2', '2025-05-15 13:38:26', '2025-05-15 13:38:26'),
(35, 'anonymous', 'saadsd', 'Others', 'sdfs', '[\"No Country of Origin\",\"Failure to Display valid copy of Accreditation Certificate in conspicuous place in the establishment\"]', 'Responded', '', '2025-05-17 00:00:00', '2025-05-19 00:00:00', '2', '2025-05-16 08:55:53', '2025-05-16 08:55:53'),
(36, 'dsdf', 'Sasuke', 'Manufacturing', 'dfsfsf', '[\"No PS/ICC Mark\",\"No Date Manufactured\",\"Price is beyond the price ceiling\",\"Operating without Business Name Registration\"]', 'Responded', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '4', '2025-05-16 08:59:53', '2025-05-16 08:59:53'),
(37, 'dsdf', 'Sasuke', 'Manufacturing', 'dfsfsf', '[\"No PS/ICC Mark\",\"No Date Manufactured\",\"Price is beyond the price ceiling\",\"Operating without Business Name Registration\"]', 'Responded', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '4', '2025-05-16 09:00:00', '2025-05-16 09:00:00'),
(38, 'dsdf', 'Sasuke', 'Manufacturing', 'dfsfsf', '[\"No PS/ICC Mark\",\"No Date Manufactured\",\"Price is beyond the price ceiling\",\"Operating without Business Name Registration\"]', 'Responded', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '4', '2025-05-16 09:00:22', '2025-05-16 09:00:22'),
(39, 'dsdf', 'Sasuke', 'Manufacturing', 'dfsfsf', '[\"No PS/ICC Mark\",\"No Date Manufactured\",\"Price is beyond the price ceiling\",\"Operating without Business Name Registration\"]', 'Responded', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '4', '2025-05-16 09:00:43', '2025-05-16 09:00:43'),
(40, 'dfsdf', 'dff', 'Food Service', 'asdfs', '[\"No PS/ICC Mark\"]', 'Responded', '', '2025-05-10 00:00:00', '2025-05-12 00:00:00', '1', '2025-05-17 14:18:33', '2025-05-17 14:18:33'),
(41, 'Ichiraku', 'Sakura', 'Food Service', 'Instant Noodles', '[\"No Manufacturer\'s Name\",\"Rubbish\"]', 'Responded', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '2', '2025-05-17 14:23:43', '2025-05-17 14:23:43'),
(42, 'Konoha', 'Naruto', 'Service and Repair', 'adasa', '[\"No PS/ICC Mark\",\"Lalalalalala\"]', 'Responded', '', '2025-05-23 00:00:00', '2025-05-25 00:00:00', '2', '2025-05-17 19:06:05', '2025-05-17 19:06:05'),
(43, 'Asad', 'Laura', 'Manufacturing', 'dsfsd', '[\"No PS/ICC Mark\"]', 'Responded', '', '2025-05-08 00:00:00', '2025-05-10 00:00:00', '1', '2025-05-17 19:20:55', '2025-05-18 11:08:16'),
(44, 'Tomb Raider', 'Lara Croft', 'Service and Repair', 'Tomb', '[\"Price grossly in excess of its\\/their true worth\",\"Price is beyond the price ceiling\"]', 'Received', '', '2025-05-16 00:00:00', '2025-05-18 00:00:00', '2', '2025-05-18 12:00:40', '2025-05-18 12:00:40'),
(45, 'Deer Farm', 'Jane Doe', 'Food Service', 'Venison', '[\"No Manufacturer\'s Address\",\"No Date Manufactured\"]', 'Refused', '', '2025-05-19 00:00:00', '2025-05-21 00:00:00', '2', '2025-05-18 12:03:47', '2025-05-18 12:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sealed` tinyint(1) DEFAULT 0,
  `withdrawn` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `pieces` int(11) DEFAULT NULL,
  `dao_violation` tinyint(1) DEFAULT 0,
  `other_violation` tinyint(1) DEFAULT 0,
  `product_remarks` varchar(250) NOT NULL,
  `inv_remarks` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `establishment_id`, `product_name`, `sealed`, `withdrawn`, `description`, `price`, `pieces`, `dao_violation`, `other_violation`, `product_remarks`, `inv_remarks`, `date_created`) VALUES
(10, 30, 'sdfs', 1, 1, 'dsfsd', 12.00, 12, 1, 0, 'sada', 'The sealed products were left', '2025-05-15 09:57:11'),
(11, 31, 'dsf', 1, 0, 'sdfs', 23.00, 231, 1, 0, 'sdfsd', 'The sealed products were left', '2025-05-15 13:27:18'),
(12, 32, 'dsf', 1, 0, 'sdfs', 23.00, 231, 1, 0, 'sdfsd', 'The sealed products were left', '2025-05-15 13:27:23'),
(13, 33, 'dsf', 1, 0, 'sdfs', 23.00, 231, 1, 0, 'sdfsd', 'The sealed products were left', '2025-05-15 13:33:27'),
(14, 36, 'sdf', 1, 1, 'sdfs', 12.00, 231, 1, 1, '1awdasf', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 08:59:53'),
(15, 36, 'sdfdsf', 1, 1, 'sdfsdf', 12.00, 1321, 1, 1, 'fdsfs', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 08:59:53'),
(16, 37, 'sdf', 1, 1, 'sdfs', 12.00, 231, 1, 1, '1awdasf', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:00'),
(17, 37, 'sdfdsf', 1, 1, 'sdfsdf', 12.00, 1321, 1, 1, 'fdsfs', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:00'),
(18, 38, 'sdf', 1, 1, 'sdfs', 12.00, 231, 1, 1, '1awdasf', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:22'),
(19, 38, 'sdfdsf', 1, 1, 'sdfsdf', 12.00, 1321, 1, 1, 'fdsfs', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:22'),
(20, 39, 'sdf', 1, 1, 'sdfs', 12.00, 231, 1, 1, '1awdasf', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:43'),
(21, 39, 'sdfdsf', 1, 1, 'sdfsdf', 12.00, 1321, 1, 1, 'fdsfs', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-16 09:00:43'),
(22, 41, 'Instant Noods', 1, 1, 'Yellow', 123.00, 123, 1, 1, 'Too salty', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-17 14:23:43'),
(23, 41, 'Charsu', 1, 1, 'pig', 1243.00, 233, 1, 1, 'Fatty', 'The sealed products were left, The withdrawn products were brought to the DTI', '2025-05-17 14:23:43'),
(24, 42, 'Rawmen', 1, 1, 'noods', 12.00, 12, 1, 1, 'dsdsd', 'The sealed products were left', '2025-05-17 19:06:05'),
(25, 45, 'Venison', 1, 1, 'dsfsd', 12.00, 12, 1, 1, 'sfdfsd', 'The withdrawn products were brought to the DTI', '2025-05-18 12:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `notice_images`
--

CREATE TABLE `notice_images` (
  `image_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_name` varchar(100) NOT NULL,
  `image_type` varchar(50) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_images`
--

INSERT INTO `notice_images` (`image_id`, `record_id`, `image_path`, `image_name`, `image_type`, `upload_date`, `active`) VALUES
(1, 2, 'uploads/notice_images/notice_6828be83817ab_my CV.docx', 'my CV.docx', 'application/vnd.openxmlformats-officedocument.word', '2025-05-17 16:51:15', 1),
(2, 8, 'uploads/notice_images/notice_682904a56454a_Maverick Ryan T. Blanco.pdf', 'Maverick Ryan T. Blanco.pdf', 'application/pdf', '2025-05-17 21:50:29', 1),
(3, 11, 'uploads/notice_images/notice_682909c92ee12_Screenshot 2025-05-18 000129.png', 'Screenshot 2025-05-18 000129.png', 'image/png', '2025-05-17 22:12:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notice_issuers`
--

CREATE TABLE `notice_issuers` (
  `issuer_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `issuer_name` varchar(255) NOT NULL,
  `issuer_position` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_issuers`
--

INSERT INTO `notice_issuers` (`issuer_id`, `establishment_id`, `issuer_name`, `issuer_position`, `created_at`) VALUES
(1, 30, 'obd', 'Team Leader', '2025-05-15 01:57:11'),
(2, 30, 'obd', 'Team Member', '2025-05-15 01:57:11'),
(3, 31, 'obd', 'Team Leader', '2025-05-15 05:27:18'),
(4, 31, 'sdf', 'Team Member', '2025-05-15 05:27:18'),
(5, 32, 'obd', 'Team Leader', '2025-05-15 05:27:23'),
(6, 32, 'sdf', 'Team Member', '2025-05-15 05:27:23'),
(7, 33, 'obd', 'Team Leader', '2025-05-15 05:33:27'),
(8, 33, 'sdf', 'Team Member', '2025-05-15 05:33:27'),
(9, 34, 'obd', 'Team Leader', '2025-05-15 05:38:26'),
(10, 34, 'obd', 'Team Member', '2025-05-15 05:38:26'),
(11, 35, 'obd', 'Team Leader', '2025-05-16 00:55:53'),
(12, 35, 'df', 'Team Member', '2025-05-16 00:55:53'),
(13, 36, 'obd', 'Team Leader', '2025-05-16 00:59:53'),
(14, 36, 'sdfsd', 'Team Member', '2025-05-16 00:59:53'),
(15, 37, 'obd', 'Team Leader', '2025-05-16 01:00:00'),
(16, 37, 'sdfsd', 'Team Member', '2025-05-16 01:00:00'),
(17, 38, 'obdsdf', 'Team Leader', '2025-05-16 01:00:22'),
(18, 38, 'sdfsd', 'Team Member', '2025-05-16 01:00:22'),
(19, 38, 'sdf', 'Team Leader', '2025-05-16 01:00:22'),
(20, 39, 'obdsdf', 'Team Leader', '2025-05-16 01:00:43'),
(21, 39, 'sdfsd', 'Team Member', '2025-05-16 01:00:43'),
(22, 39, 'sdf', 'Team Leader', '2025-05-16 01:00:43'),
(23, 40, 'obd', 'Team Leader', '2025-05-17 06:18:33'),
(24, 40, 'adsf', 'Team Member', '2025-05-17 06:18:33'),
(25, 41, 'obd', 'Team Leader', '2025-05-17 06:23:43'),
(26, 41, 'df', 'Team Member', '2025-05-17 06:23:43'),
(27, 41, 'adsf', 'Team Member', '2025-05-17 06:23:43'),
(28, 42, 'abc', 'Team Leader', '2025-05-17 11:06:05'),
(29, 42, 'def', 'Team Member', '2025-05-17 11:06:05'),
(30, 43, 'obd', 'Team Leader', '2025-05-17 11:20:55'),
(31, 43, 'abc', 'Team Member', '2025-05-17 11:20:55'),
(32, 44, 'obd', 'Team Leader', '2025-05-18 04:00:40'),
(33, 44, 'adsf', 'Team Member', '2025-05-18 04:00:40'),
(34, 45, 'obd', 'Team Member', '2025-05-18 04:03:47'),
(35, 45, 'sdfsd', 'Team Member', '2025-05-18 04:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `notice_records`
--

CREATE TABLE `notice_records` (
  `record_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `notice_type` varchar(100) NOT NULL,
  `date_responded` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_records`
--

INSERT INTO `notice_records` (`record_id`, `establishment_id`, `notice_type`, `date_responded`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 43, 'Compliance', '2025-05-18 00:00:00', 'Responded', 'success', '2025-05-17 16:47:34', '2025-05-18 00:50:36'),
(2, 42, 'Compliance', '2025-05-18 00:00:00', 'Responded', 'edwed', '2025-05-17 16:51:15', '2025-05-18 05:47:28'),
(3, 41, 'Compliance', '2025-05-14 00:00:00', 'Responded', 'cxxc', '2025-05-17 16:51:38', '2025-05-18 00:51:53'),
(4, 40, 'Other', '2025-05-19 00:00:00', 'Responded', 'Details: yugyu\ngyug', '2025-05-17 16:53:00', '2025-05-18 00:53:00'),
(5, 38, 'CFO', '2025-05-19 00:00:00', 'Responded', '', '2025-05-17 17:20:55', '2025-05-18 05:55:59'),
(6, 35, 'CFO', '2025-05-08 00:00:00', 'Responded', '', '2025-05-17 21:49:47', '2025-05-18 05:49:47'),
(7, 36, 'CFO', '2025-05-19 00:00:00', 'Responded', '', '2025-05-17 21:50:06', '2025-05-18 05:50:06'),
(8, 39, 'FC', '2025-05-18 00:00:00', 'Responded', '', '2025-05-17 21:50:29', '2025-05-18 05:50:29'),
(9, 37, 'Other', '2025-05-05 00:00:00', 'Responded', 'Details: yugyu\nfgdf', '2025-05-17 21:56:28', '2025-05-18 05:56:28'),
(10, 34, 'Certified First Offence', '2025-05-18 00:00:00', 'Responded', '', '2025-05-17 22:11:44', '2025-05-18 06:11:44'),
(11, 33, 'Formal Charge', '2025-05-18 00:00:00', 'Responded', '', '2025-05-17 22:12:25', '2025-05-18 06:12:25');

-- --------------------------------------------------------

--
-- Table structure for table `notice_status`
--

CREATE TABLE `notice_status` (
  `notice_stat_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `witnessed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_status`
--

INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `witnessed_by`, `created_at`, `updated_at`) VALUES
(31, 30, 'Received', NULL, '2025-05-15 01:57:11', '2025-05-15 09:57:11'),
(32, 31, 'Received', '', '2025-05-15 05:27:18', '2025-05-15 13:27:18'),
(33, 32, 'Received', '', '2025-05-15 05:27:23', '2025-05-15 13:27:23'),
(34, 33, 'Received', '', '2025-05-15 05:33:27', '2025-05-15 13:33:27'),
(35, 34, 'Received', '', '2025-05-15 05:38:26', '2025-05-15 13:38:26'),
(36, 35, 'Received', NULL, '2025-05-16 00:55:54', '2025-05-16 08:55:54'),
(37, 36, 'Received', NULL, '2025-05-16 00:59:53', '2025-05-16 08:59:53'),
(38, 37, 'Received', NULL, '2025-05-16 01:00:00', '2025-05-16 09:00:00'),
(39, 38, 'Received', NULL, '2025-05-16 01:00:22', '2025-05-16 09:00:22'),
(40, 39, 'Received', NULL, '2025-05-16 01:00:43', '2025-05-16 09:00:43'),
(41, 40, 'Received', NULL, '2025-05-17 06:18:33', '2025-05-17 14:18:33'),
(42, 41, 'Received', NULL, '2025-05-17 06:23:43', '2025-05-17 14:23:43'),
(43, 42, 'Received', NULL, '2025-05-17 11:06:05', '2025-05-17 19:06:05'),
(44, 43, 'Received', NULL, '2025-05-17 11:20:55', '2025-05-17 19:20:55'),
(45, 44, 'Received', NULL, '2025-05-18 04:00:40', '2025-05-18 12:00:40'),
(46, 45, 'Refused', 'sdfsdf', '2025-05-18 04:03:47', '2025-05-18 12:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `penalty_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Unpaid',
  `issued_by` varchar(100) DEFAULT NULL,
  `issued_date` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalties`
--

INSERT INTO `penalties` (`penalty_id`, `establishment_id`, `amount`, `description`, `reference_number`, `status`, `issued_by`, `issued_date`, `created_at`) VALUES
(1, 43, 100.00, 'too high', '123455', 'Unpaid', 'asdas', '2025-05-18 10:23:39', '2025-05-18 10:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `ulvl` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
(1, 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', '', 'admin', 'active', ''),
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', 'inspector', 'active', ''),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani'),
(7, 'ukelele', '$2y$10$kZ0inWovACx0AZS1zvrNQOi8LhI33HM4N7KHrzqNw8QwWHDLSYTNy', 'Alto Cleff', 'inspector', 'active', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `logs_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
(202, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 07:21:45'),
(203, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 13:21:27'),
(204, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-10 13:31:34'),
(205, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 13:31:42'),
(206, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 14:49:53'),
(207, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 15:10:23'),
(208, 0, 'Log Maintenance', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Automatically deleted 0 logs older than 30 days', '2025-05-10 15:14:46'),
(209, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 15:50:41'),
(210, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-10 15:50:54'),
(211, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 20:50:06'),
(212, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-10 21:13:11'),
(213, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-11 17:19:51'),
(214, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-11 17:20:54'),
(215, 7, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User ukelele (Alto Cleff) logged in successfully', '2025-05-11 17:21:05'),
(216, 7, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User ukelele (Alto Cleff) logged out', '2025-05-11 17:21:14'),
(217, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-11 17:21:20'),
(218, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 07:46:05'),
(219, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 08:44:54'),
(220, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 09:55:29'),
(221, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 27 - Kawaii', '2025-05-13 09:55:33'),
(222, 1, 'Failed Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Failed login attempt for username: admin - Invalid password', '2025-05-13 13:49:35'),
(223, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 13:49:42'),
(224, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 27 - Kawaii', '2025-05-13 13:49:47'),
(225, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 27 - Kawaii', '2025-05-13 13:51:15'),
(226, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-13 14:00:19'),
(227, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 14:01:24'),
(228, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-13 14:02:05'),
(229, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 14:14:08'),
(230, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-13 14:14:25'),
(231, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 14:15:12'),
(232, 0, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 28 - show shop', '2025-05-13 14:32:32'),
(233, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 14:39:42'),
(234, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 28 - show shop', '2025-05-13 14:44:48'),
(235, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 28 - show shop', '2025-05-13 14:51:07'),
(236, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 28 - show shop', '2025-05-13 14:51:34'),
(237, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-13 15:00:11'),
(238, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 15:08:14'),
(239, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 28 - show shop', '2025-05-13 15:11:04'),
(240, 1, 'Viewed establishment', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Viewed establishment ID: 29 - sari sari store', '2025-05-13 15:22:19'),
(241, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-13 16:21:47'),
(242, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 10:57:25'),
(243, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 11:10:08'),
(244, 1, 'Failed Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Failed login attempt for username: admin - Invalid password', '2025-05-14 11:31:59'),
(245, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 11:32:07'),
(246, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 14:17:26'),
(247, 1, 'Failed Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Failed login attempt for username: admin - Invalid password', '2025-05-14 15:10:58'),
(248, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 15:11:04'),
(249, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-14 15:41:18'),
(250, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-15 09:44:33'),
(251, 0, 'CREATE_NOTICE', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created notice for establishment: scp', '2025-05-15 13:27:18'),
(252, 0, 'CREATE_NOTICE', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created notice for establishment: scp', '2025-05-15 13:27:23'),
(253, 0, 'CREATE_NOTICE', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created notice for establishment: scp', '2025-05-15 13:33:27'),
(254, 0, 'CREATE_NOTICE', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created notice for establishment: anonymous', '2025-05-15 13:38:26'),
(255, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-15 14:37:04'),
(256, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-15 15:50:09'),
(257, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-15 16:20:28'),
(258, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-16 08:20:02'),
(259, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-16 08:57:30'),
(260, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 13:40:04'),
(261, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 13:51:19'),
(262, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 14:18:01'),
(263, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 19:02:09'),
(264, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 19:20:10'),
(265, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 19:40:29'),
(266, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 20:09:14'),
(267, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-17 23:58:45'),
(268, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 00:10:52'),
(269, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 00:25:48'),
(270, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 00:46:37'),
(271, 1, 'Updated action type to Compliance', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 43 action type to: Compliance', '2025-05-18 00:47:34'),
(272, 1, 'Updated action type to Compliance', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 43 action type to: Compliance', '2025-05-18 00:50:36'),
(273, 1, 'Updated action type to FC', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 42 action type to: FC', '2025-05-18 00:51:15'),
(274, 1, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 41 action type to: CFO', '2025-05-18 00:51:38'),
(275, 1, 'Updated action type to Compliance', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 41 action type to: Compliance', '2025-05-18 00:51:53'),
(276, 1, 'Updated action type to Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 40 action type to: Other', '2025-05-18 00:53:00'),
(277, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 01:18:39'),
(278, 1, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 42 action type to: CFO', '2025-05-18 01:20:22'),
(279, 1, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 38 action type to: CFO', '2025-05-18 01:20:55'),
(280, 0, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 42 action type to: CFO', '2025-05-18 05:47:13'),
(281, 0, 'Updated action type to Compliance', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 42 action type to: Compliance', '2025-05-18 05:47:28'),
(282, 0, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 35 action type to: CFO', '2025-05-18 05:49:47'),
(283, 0, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 36 action type to: CFO', '2025-05-18 05:50:06'),
(284, 0, 'Updated action type to FC', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 39 action type to: FC', '2025-05-18 05:50:29'),
(285, 0, 'Updated action type to CFO', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 38 action type to: CFO', '2025-05-18 05:55:59'),
(286, 0, 'Updated action type to Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 37 action type to: Other', '2025-05-18 05:56:28'),
(287, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 06:10:51'),
(288, 1, 'Updated action type to Certified First Offence', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 34 action type to: Certified First Offence', '2025-05-18 06:11:44'),
(289, 1, 'Updated action type to Formal Charge', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Updated establishment ID: 33 action type to: Formal Charge', '2025-05-18 06:12:25'),
(290, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 09:37:14'),
(291, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 10:05:49'),
(292, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 10:23:12'),
(293, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 11:04:48'),
(294, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-18 11:54:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`establishment_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `notice_images`
--
ALTER TABLE `notice_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `notice_issuers`
--
ALTER TABLE `notice_issuers`
  ADD PRIMARY KEY (`issuer_id`),
  ADD KEY `establishment_id` (`establishment_id`);

--
-- Indexes for table `notice_records`
--
ALTER TABLE `notice_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `establishment_id` (`establishment_id`);

--
-- Indexes for table `notice_status`
--
ALTER TABLE `notice_status`
  ADD PRIMARY KEY (`notice_stat_id`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `establishment_id` (`establishment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`logs_id`),
  ADD KEY `idx_user_logs_user_id` (`user_id`),
  ADD KEY `idx_user_logs_action` (`action`),
  ADD KEY `idx_user_logs_timestamp` (`timestamp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `establishment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notice_images`
--
ALTER TABLE `notice_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notice_issuers`
--
ALTER TABLE `notice_issuers`
  MODIFY `issuer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `notice_records`
--
ALTER TABLE `notice_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notice_status`
--
ALTER TABLE `notice_status`
  MODIFY `notice_stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `logs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notice_images`
--
ALTER TABLE `notice_images`
  ADD CONSTRAINT `notice_images_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `notice_records` (`record_id`) ON DELETE CASCADE;

--
-- Constraints for table `notice_issuers`
--
ALTER TABLE `notice_issuers`
  ADD CONSTRAINT `notice_issuers_ibfk_1` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`establishment_id`) ON DELETE CASCADE;

--
-- Constraints for table `notice_records`
--
ALTER TABLE `notice_records`
  ADD CONSTRAINT `notice_records_ibfk_1` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`establishment_id`) ON DELETE CASCADE;

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`establishment_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<<<<<<<< HEAD:public/backups/temp/pre_restore_backup_2025-05-09_09-00-03.sql
-- Database Backup for nov_system7
-- Generated: 2025-05-09 09:00:03
========
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 07:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
>>>>>>>> 213e80d030f679110bdbf8171d53f7dfc84f749c:public/backups/uploaded_2025-05-03_19-24-15.sql

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
<<<<<<<< HEAD:public/backups/temp/pre_restore_backup_2025-05-09_09-00-03.sql
  `region` varchar(100) NOT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `inventory`
DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `establishment_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sealed` tinyint(1) DEFAULT 0,
  `withdrawn` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `pieces` int(11) DEFAULT NULL,
  `dao_violation` tinyint(1) DEFAULT 0,
  `other_violation` tinyint(1) DEFAULT 0,
  `inv_remarks` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `notice_status`
DROP TABLE IF EXISTS `notice_status`;
CREATE TABLE `notice_status` (
  `notice_stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `establishment_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `issued_datetime` datetime DEFAULT NULL,
  `witnessed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`notice_stat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `user_logs`
DROP TABLE IF EXISTS `user_logs`;
CREATE TABLE `user_logs` (
  `logs_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`logs_id`),
  KEY `idx_user_logs_user_id` (`user_id`),
  KEY `idx_user_logs_action` (`action`),
  KEY `idx_user_logs_timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
-- 5 rows
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
(1, 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', '', 'admin', 'active', ''),
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', 'inspector', 'active', ''),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani');

-- Data for table `addresses`
-- 26 rows
========
  `region` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

>>>>>>>> 213e80d030f679110bdbf8171d53f7dfc84f749c:public/backups/uploaded_2025-05-03_19-24-15.sql
INSERT INTO `addresses` (`address_id`, `establishment_id`, `street`, `barangay`, `municipality`, `province`, `region`) VALUES
(1, 0, 'asdas', 'asda', 'asda', 'dsada', 'asdasa'),
(2, 0, 'sdf', 'sdf', 'fdsf', 'dfsdfs', 'sdfs'),
(3, 0, 'asd', 'asd', 'asd', 'asdasd', 'sdas'),
(4, 4, 'sdf', 'df', 'dsf', 'leaf', 'konoha'),
(5, 5, 'dfdsfs', 'fsfsfs', 'fsfs', 'dfsfsdfds', 'sdfsd'),
(6, 6, 'fgdfgdf', 'gdfgd', 'gdfgdgfd', 'fgdfgfd', 'dfgdgd'),
(7, 7, 'wer', 'ewr', 'werw', 'ewwe', 'werww'),
(8, 8, 'delussion', 'land', 'believe', 'make', '2'),
(9, 9, 'sdfsdfs', 'fsdfsdf', 'fsdfsdfsd', 'fdsfdsfsd', 'dsvdsvsd'),
(10, 10, 'vxcvx', 'cxvxvzx', 'vzxvxcv', 'vzxcvzxcvczx', 'xzcvzx'),
(11, 11, 'dsfsd', 'sdfs', 'dfsdf', 'fsdfsdfsd', 'dfsdfsd'),
(12, 12, 'fasfaf', 'asfa', 'fasfas', 'fafasfas', 'sfas'),
(13, 13, 'dsf', 'dsf', 'sdf', 'dfs', 'asd'),
(14, 14, 'dsfsdfsdfsdfsd', 'fsdfsdf', 'sdfsdf', 'fsdfsdf', 'dfsdfsdfsd'),
(15, 15, 'sdgsd', 'dsgds', 'gsgds', 'sdgs', 'gsgsgsdg'),
(16, 16, 'asf', 'asf', 'asdf', 'asfd', 'asdfs'),
(17, 17, 'adf', 'asdf', 'sadf', 'asdf', 'adfs'),
(18, 18, 'xbfxb', 'Quirino', 'Tagudin', 'ilocos sur', '1'),
(19, 19, 'From Reality', 'Away', 'Far', 'far', 'Somewhere'),
<<<<<<<< HEAD:public/backups/temp/pre_restore_backup_2025-05-09_09-00-03.sql
(20, 20, 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg'),
(21, 21, 'sdf', 'sdfs', 'dsfs', 'falls', 'swallow'),
(22, 22, 'asd', 'df', 'fdsf', 'dfsdfs', 'sdfs'),
(23, 23, 'far away', 'far', 'everywhere', 'anywhere', 'somewhere'),
(24, 24, 'ert', 'wrt', 'wret', 'wrtwe', 'retert'),
(25, 25, 'WER', 'WER', 'WER', 'WE', 'EW'),
(26, 26, 'dfgdgd', 'dfgd', 'dgdf', 'dgd', 'dfgd');
========
(20, 20, 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg');

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
  `nov_files` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `issued_by` varchar(255) DEFAULT NULL,
  `issued_datetime` datetime DEFAULT NULL,
  `num_violations` varchar(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`establishment_id`, `name`, `owner_representative`, `nature`, `products`, `violations`, `notice_status`, `remarks`, `nov_files`, `created_at`, `issued_by`, `issued_datetime`, `num_violations`, `date_created`, `date_updated`) VALUES
(1, 'asad', 'Mister Krabs', 'Service and Repair', 'asdasda', 'No PS/ICC Mark', 'Received', 'asd', 'asad_1745802355.txt', '2025-04-28 01:06:18', 'asda', '2025-04-28 09:06:07', NULL, '2025-04-28 01:06:18', '2025-04-28 01:06:18'),
(2, 'Meliodas', 'humphry', 'Supermarket/Grocery/Convenience Store', 'sdfs', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Received', 'dfsdfs', 'Meliodas_1745802459.txt', '2025-04-28 01:07:55', 'adas', '2025-04-28 09:07:48', NULL, '2025-04-28 01:07:55', '2025-04-28 01:07:55'),
(3, 'asdaa', 'sdasd', 'Service and Repair', 'asda', 'Invalid/suspended or cancelled BPS license', 'Refused', '', 'asdaa_1745802539.txt', '2025-04-28 01:09:04', '', '2025-04-28 09:09:00', NULL, '2025-04-28 01:09:04', '2025-04-28 01:09:04'),
(4, 'ichiraku ramen', 'naruto', 'Service and Repair', 'sdfs', 'No Manufacturer&#039;s Address', 'Received', '', 'ichirakuramen_1745803167.txt', '2025-04-28 01:19:38', 'rgr', '2025-04-28 09:19:29', NULL, '2025-04-28 01:19:38', '2025-04-28 01:19:38'),
(5, 'dsfs', 'dfsdf', 'Retail/Wholesaler', 'toys', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Received', 'asasadsds', 'dsfs_1745803324.txt', '2025-04-28 01:22:28', 'sdfsd', '2025-04-28 09:22:20', NULL, '2025-04-28 01:22:28', '2025-04-28 01:22:28'),
(6, 'gdfgdf', 'fdgddf', 'Service and Repair', 'dfgdfg', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Refused', 'not good enough', 'gdfgdf_1745803418.txt', '2025-04-28 01:24:08', '', '2025-04-28 09:24:02', NULL, '2025-04-28 01:24:08', '2025-04-28 01:24:08'),
(7, 'erew', 'saadsd', 'Supermarket/Grocery/Convenience Store', 'ewrw', 'No PS/ICC Mark', 'Received', 'erwer', 'erew_1745801962.txt', '2025-04-28 01:36:50', 'asdas', '2025-04-28 08:59:31', NULL, '2025-04-28 01:36:50', '2025-04-28 01:36:50'),
(8, 'Pete&#039;s Pizzeria and Abortion Clinic', 'Pete', 'food', 'pizza sauce', 'No Manufacturer&#039;s Name, No Product Contents/Ingredients', 'Refused', 'disgusting', 'Pete039sPizzeriaandAbortionClinic_1745804315.txt', '2025-04-28 01:38:55', '', '2025-04-28 09:38:49', NULL, '2025-04-28 01:38:55', '2025-04-28 01:38:55'),
(9, 'Boar&#039;s Hut', 'Meliodas', 'Hardware', 'Ale', 'No Manufacturer&#039;s Name, No Batch Number/Lot Code', 'Refused', 'ssssssss', 'Boar039sHut_1745825682.txt', '2025-04-28 07:35:02', '', '2025-04-28 15:34:45', NULL, '2025-04-28 07:35:02', '2025-04-28 07:35:02'),
(10, 'cvzxcv', 'cvzxv', 'Service and Repair', 'zxcv', 'No Manufacturer&#039;s Name, No Country of Origin', 'Refused', '', 'cvzxcv_1745825782.txt', '2025-04-28 07:36:55', '', '2025-04-02 08:36:00', NULL, '2025-04-28 07:36:55', '2025-04-28 07:36:55'),
(11, 'Casita\'s mansion', 'Pepa', 'Hardware', 'sdf', 'No PS/ICC Mark, No Manufacturer\'s Address', 'Received', '', 'Casitasmansion_1745826429.txt', '2025-04-28 07:47:19', 'zxcz', '2025-04-28 15:47:12', NULL, '2025-04-28 07:47:19', '2025-04-28 07:47:19'),
(12, 'dota shop', 'shopkeeper', 'Retail/Wholesaler', 'Dangerous Items', 'No Manufacturer\'s Address, No Accreditation Certification', 'Received', '', 'dotashop_1745827482.txt', '2025-04-28 08:04:56', 'asdas', '2025-04-25 16:04:00', NULL, '2025-04-28 08:04:56', '2025-04-28 08:04:56'),
(13, 'fsdfs', 'meliodafu', 'Hardware', 'dsfsdf', 'Expired Accreditation Certificate, Freight Business with Failure to Display valid copy of Accreditation Certificate in conspicuous place in the establishment.', 'Received', 'dsfdsf', 'fsdfs_1745827904.txt', '2025-04-28 08:12:32', 'Ah', '2025-04-28 16:11:48', NULL, '2025-04-28 08:12:32', '2025-04-28 08:12:32'),
(14, 'fsdfsdfsdf', 'dfsdfs', 'Manufacturing', 'dsfsdf', 'Price grossly in excess of its/their true worth, Engaging in business using trade name on signages and/or documents without prior registration, Failure to Display Business Name Certificate, Conducting Sales Promotion without Sales Promotion Permit.', 'Received', 'fgdfgfdgf', 'fsdfsdfsdf_1745829861.txt', '2025-04-28 08:44:33', 'me', '2025-04-28 16:44:23', NULL, '2025-04-28 08:44:33', '2025-04-28 08:44:33'),
(15, 'gdsgsgsdgs', 'gasgsd', 'Supermarket/Grocery/Convenience Store', 'sdg', 'Conducting Sales Promotion without Sales Promotion Permit.', 'Refused', 'sdfsd', 'gdsgsgsdgs_1745892917.txt', '2025-04-29 02:15:28', '', '2025-04-29 10:15:20', NULL, '2025-04-29 02:15:28', '2025-04-29 02:15:28'),
(16, 'dsfs', 'Boar Hut', 'Hardware', 'adf', '', 'Received', '', 'dsfs_1745893407.txt', '2025-04-29 02:23:34', 'dasf', '2025-04-29 10:23:28', NULL, '2025-04-29 02:23:34', '2025-04-29 02:23:34'),
(17, 'sadfs', 'dfsda', 'Supermarket/Grocery/Convenience Store', 'adfas', 'Conducting Sales Promotion without Sales Promotion Permit.', 'Received', '', 'sadfs_1745894044.txt', '2025-04-29 02:34:16', 'asdf', '2025-04-29 10:34:09', NULL, '2025-04-29 02:34:16', '2025-04-29 02:34:16'),
(18, 'cgd', 'tdh', 'Manufacturing', 'toys', 'Failure to Display Business Name Certificate', 'Refused', '', 'cgd_1745906632.txt', '2025-04-29 06:04:11', '', '2025-04-21 14:03:00', NULL, '2025-04-29 06:04:11', '2025-04-29 06:04:11'),
(19, 'Serpent\'s Library', 'LS', 'Retail/Wholesaler', 'Books', 'No Manufacturer\'s Name', 'Received', 'breaks reality', 'SerpentsLibrary_1745913163.txt', '2025-04-29 07:52:43', 'Cleff', '2025-04-29 15:50:59', NULL, '2025-04-29 15:52:43', '2025-04-29 15:52:43'),
(20, 'dfgdfg', 'dgsdg', 'Service and Repair', 'dfgdf', 'Price is beyond the price cieling', 'Refused', '', 'dfgdfg_1746172610.txt', '2025-05-02 07:56:50', '', '2025-05-01 15:56:00', NULL, '2025-05-02 15:56:50', '2025-05-02 15:56:50');

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
  `inv_remarks` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--
>>>>>>>> 213e80d030f679110bdbf8171d53f7dfc84f749c:public/backups/uploaded_2025-05-03_19-24-15.sql

INSERT INTO `inventory` (`inventory_id`, `establishment_id`, `product_name`, `sealed`, `withdrawn`, `description`, `price`, `pieces`, `dao_violation`, `other_violation`, `inv_remarks`, `date_created`) VALUES
(1, 0, 'dsfsdf', 0, 0, '0', 11.00, 22, 1, 0, '22awdsa', '2025-04-28 01:06:18'),
(2, 0, 'dfsdfs', 1, 0, '0', 12.00, 0, 1, 0, 'dwq', '2025-04-28 01:07:55'),
(3, 5, 'toys', 0, 1, '0', 1223.00, 123, 0, 1, 'sfsdfs', '2025-04-28 01:22:28'),
(4, 6, 'sdgfdfg', 1, 1, '0', 12.00, 12, 1, 1, 'fsdfsd', '2025-04-28 01:24:08'),
(5, 6, 'dsdfsd', 1, 1, '0', 12.00, 12, 1, 0, 'sfdsfs', '2025-04-28 01:24:08'),
(6, 7, 'werw', 1, 0, '0', 12.00, 12, 1, 0, '12qweqw', '2025-04-28 01:36:50'),
(7, 8, 'sdas', 1, 1, '0', 12.00, 12, 1, 0, 'asdasda', '2025-04-28 01:38:55');

<<<<<<<< HEAD:public/backups/temp/pre_restore_backup_2025-05-09_09-00-03.sql
-- Data for table `notice_status`
-- 26 rows
INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
(1, 0, 'Received', 'asda', 'asdas', '2025-04-28 09:06:07', '', '2025-04-30 01:06:18', '2025-04-28 01:06:18'),
(2, 0, 'Received', 'adas', 'sadas', '2025-04-28 09:07:48', '', '2025-04-30 01:07:55', '2025-04-28 01:07:55'),
(3, 0, 'Refused', '', '', '2025-04-28 09:09:00', 'me', '2025-04-30 01:09:04', '2025-04-28 01:09:04'),
(4, 4, 'Received', 'rgr', 'fsdfds', '2025-04-28 09:19:29', '', '2025-04-30 01:19:38', '2025-04-28 01:19:38'),
(5, 5, 'Received', 'sdfsd', 'missionary', '2025-04-28 09:22:20', '', '2025-04-30 01:22:28', '2025-04-28 01:22:28'),
(6, 6, 'Refused', '', '', '2025-04-28 09:24:02', 'shi', '2025-04-30 01:24:08', '2025-04-28 01:24:08'),
(7, 7, 'Received', 'asdas', 'werwr', '2025-04-28 08:59:31', '', '2025-04-30 01:36:50', '2025-04-28 01:36:50'),
(8, 8, 'Refused', '', '', '2025-04-28 09:38:49', 'you', '2025-04-30 01:38:55', '2025-04-28 01:38:55'),
(9, 9, 'Refused', '', '', '2025-04-28 15:34:45', 'Aym Yu', '2025-04-30 07:35:02', '2025-04-28 07:35:02'),
(10, 10, 'Refused', '', '', '2025-04-02 08:36:00', 'Naruto', '2025-04-30 07:36:55', '2025-04-28 07:36:55'),
(11, 11, 'Received', 'zxcz', 'werwr', '2025-04-28 15:47:12', '', '2025-04-30 07:47:19', '2025-04-28 07:47:19'),
(12, 12, 'Received', 'asdas', 'sd', '2025-04-25 16:04:00', '', '2025-04-30 08:04:56', '2025-04-28 08:04:56'),
(13, 13, 'Received', 'Ah', 'Supervisor', '2025-04-28 16:11:48', '', '2025-04-30 08:12:32', '2025-04-28 08:12:32'),
(14, 14, 'Received', 'me', 'myself', '2025-04-28 16:44:23', '', '2025-04-30 08:44:33', '2025-04-28 08:44:33'),
(15, 15, 'Refused', '', '', '2025-04-29 10:15:20', 'aym yu', '2025-05-01 02:15:28', '2025-04-29 02:15:28'),
(16, 16, 'Received', 'dasf', 'adf', '2025-04-29 10:23:28', '', '2025-05-01 02:23:34', '2025-04-29 02:23:34'),
(17, 17, 'Received', 'asdf', 'afdas', '2025-04-29 10:34:09', '', '2025-05-01 02:34:16', '2025-04-29 02:34:16'),
(18, 18, 'Refused', '', '', '2025-04-21 14:03:00', 'ed', '2025-05-01 06:04:11', '2025-04-29 06:04:11'),
(19, 19, 'Received', 'Cleff', 'O5-13', '2025-04-29 15:50:59', '', '2025-05-01 07:52:43', '2025-04-29 15:52:43'),
(20, 20, 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-04 07:56:50', '2025-05-02 15:56:50'),
(21, 21, 'Refused', '', '', '2025-05-02 11:34:00', 'Aym Yu', '2025-05-07 19:36:21', '2025-05-07 11:36:21'),
(22, 22, 'Received', 'rgr', 'asda', '2025-05-05 13:55:00', '', '2025-05-07 21:54:10', '2025-05-07 13:54:10'),
(23, 23, 'Refused', '', '', '2025-05-05 14:05:00', 'Till', '2025-05-07 21:58:34', '2025-05-07 13:58:34'),
(24, 24, 'Refused', '', '', '2025-05-05 14:50:00', 'dfgd', '2025-05-07 22:41:36', '2025-05-07 14:41:36'),
(25, 25, 'Refused', '', '', '2025-05-08 08:13:00', 'AYM YU', '2025-05-09 16:14:50', '2025-05-09 08:14:50'),
(26, 26, 'Received', 'dfg', 'dfgd', '2025-05-07 08:15:00', '', '2025-05-09 16:15:45', '2025-05-09 08:15:45');

-- Data for table `user_logs`
-- 65 rows
========
-- --------------------------------------------------------

--
-- Table structure for table `notice_status`
--

CREATE TABLE `notice_status` (
  `notice_stat_id` int(11) NOT NULL,
  `establishment_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `issued_datetime` datetime DEFAULT NULL,
  `witnessed_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_status`
--

INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
(1, 0, 'Received', 'asda', 'asdas', '2025-04-28 09:06:07', '', '2025-04-28 01:06:18', '2025-04-28 01:06:18'),
(2, 0, 'Received', 'adas', 'sadas', '2025-04-28 09:07:48', '', '2025-04-28 01:07:55', '2025-04-28 01:07:55'),
(3, 0, 'Refused', '', '', '2025-04-28 09:09:00', 'me', '2025-04-28 01:09:04', '2025-04-28 01:09:04'),
(4, 4, 'Received', 'rgr', 'fsdfds', '2025-04-28 09:19:29', '', '2025-04-28 01:19:38', '2025-04-28 01:19:38'),
(5, 5, 'Received', 'sdfsd', 'missionary', '2025-04-28 09:22:20', '', '2025-04-28 01:22:28', '2025-04-28 01:22:28'),
(6, 6, 'Refused', '', '', '2025-04-28 09:24:02', 'shi', '2025-04-28 01:24:08', '2025-04-28 01:24:08'),
(7, 7, 'Received', 'asdas', 'werwr', '2025-04-28 08:59:31', '', '2025-04-28 01:36:50', '2025-04-28 01:36:50'),
(8, 8, 'Refused', '', '', '2025-04-28 09:38:49', 'you', '2025-04-28 01:38:55', '2025-04-28 01:38:55'),
(9, 9, 'Refused', '', '', '2025-04-28 15:34:45', 'Aym Yu', '2025-04-28 07:35:02', '2025-04-28 07:35:02'),
(10, 10, 'Refused', '', '', '2025-04-02 08:36:00', 'Naruto', '2025-04-28 07:36:55', '2025-04-28 07:36:55'),
(11, 11, 'Received', 'zxcz', 'werwr', '2025-04-28 15:47:12', '', '2025-04-28 07:47:19', '2025-04-28 07:47:19'),
(12, 12, 'Received', 'asdas', 'sd', '2025-04-25 16:04:00', '', '2025-04-28 08:04:56', '2025-04-28 08:04:56'),
(13, 13, 'Received', 'Ah', 'Supervisor', '2025-04-28 16:11:48', '', '2025-04-28 08:12:32', '2025-04-28 08:12:32'),
(14, 14, 'Received', 'me', 'myself', '2025-04-28 16:44:23', '', '2025-04-28 08:44:33', '2025-04-28 08:44:33'),
(15, 15, 'Refused', '', '', '2025-04-29 10:15:20', 'aym yu', '2025-04-29 02:15:28', '2025-04-29 02:15:28'),
(16, 16, 'Received', 'dasf', 'adf', '2025-04-29 10:23:28', '', '2025-04-29 02:23:34', '2025-04-29 02:23:34'),
(17, 17, 'Received', 'asdf', 'afdas', '2025-04-29 10:34:09', '', '2025-04-29 02:34:16', '2025-04-29 02:34:16'),
(18, 18, 'Refused', '', '', '2025-04-21 14:03:00', 'ed', '2025-04-29 06:04:11', '2025-04-29 06:04:11'),
(19, 19, 'Received', 'Cleff', 'O5-13', '2025-04-29 15:50:59', '', '2025-04-29 07:52:43', '2025-04-29 15:52:43'),
(20, 20, 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-02 07:56:50', '2025-05-02 15:56:50');

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
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', '', '', ''),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani.mo'),
(6, 'cleff', '$2y$10$M.xmt1vfu2GE7bUUrzDmKOh08ShUGURUkXX1DblSJ9rMjZJpMnw0K', 'anonymous', 'inspector', 'active', 'suevos@tempmailto.org');

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

>>>>>>>> 213e80d030f679110bdbf8171d53f7dfc84f749c:public/backups/uploaded_2025-05-03_19-24-15.sql
INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
(23, 6, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged in successfully', '2025-05-03 09:51:24'),
(24, 6, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged out', '2025-05-03 09:51:55'),
(25, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 09:53:02'),
(26, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-03 10:20:37'),
(27, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 10:21:46'),
(28, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 14:20:28'),
(29, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:00:42'),
(30, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:11:25'),
(31, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 19:10:06'),
(32, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-07.sql', '2025-05-03 19:15:07'),
(33, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-12.sql', '2025-05-03 19:15:12'),
(34, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-13.sql', '2025-05-03 19:15:13'),
(35, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-26.sql', '2025-05-03 19:15:26'),
(36, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-37.sql', '2025-05-03 19:16:37'),
(37, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-39.sql', '2025-05-03 19:16:39'),
(38, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-53.sql', '2025-05-03 19:16:53'),
(39, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-07.sql', '2025-05-03 19:21:07'),
(40, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-49.sql', '2025-05-03 19:21:49'),
(41, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-53.sql', '2025-05-03 19:21:53'),
<<<<<<<< HEAD:public/backups/temp/pre_restore_backup_2025-05-09_09-00-03.sql
(42, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_19-21-53.sql', '2025-05-03 19:22:01'),
(43, 1, 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-33-33.sql', '2025-05-03 19:33:33'),
(44, 1, 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-33-37.sql', '2025-05-03 19:33:37'),
(45, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-33-39.sql', '2025-05-03 19:33:39'),
(46, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_19-33-39.sql', '2025-05-03 19:33:48'),
(47, 1, 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-34-43.sql', '2025-05-03 19:34:43'),
(48, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 20:55:16'),
(49, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_20-55-38.sql', '2025-05-03 20:55:38'),
(50, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_20-55-38.sql', '2025-05-03 20:55:50'),
(51, 1, 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_20-56-03.sql', '2025-05-03 20:56:03'),
(52, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_21-08-39.sql', '2025-05-03 21:08:39'),
(53, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 21:08:46'),
(135, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-06_15-40-07.sql', '2025-05-06 15:40:07'),
(136, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-06_15-40-07.sql', '2025-05-06 15:40:10'),
(137, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-06_15-46-13.sql', '2025-05-06 15:46:13'),
(138, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-06_15-46-15.sql', '2025-05-06 15:46:15'),
(139, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-06_15-46-15.sql', '2025-05-06 15:46:18'),
(142, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-06 16:09:14'),
(143, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-06 16:09:21'),
(144, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-06_16-06-59.sql', '2025-05-06 16:10:44'),
(145, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_14-50-51.sql', '2025-05-06 16:11:20'),
(146, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_14-51-09.sql', '2025-05-06 16:11:23'),
(147, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-00-25.sql', '2025-05-06 16:11:26'),
(148, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-22-49.sql', '2025-05-06 16:11:28'),
(149, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-23-02.sql', '2025-05-06 16:11:31'),
(150, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: uploaded_2025-05-06_15-27-04.sql', '2025-05-06 16:11:33'),
(151, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-40-07.sql', '2025-05-06 16:11:35'),
(152, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-46-13.sql', '2025-05-06 16:11:38'),
(153, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-46-15.sql', '2025-05-06 16:11:40'),
(154, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_15-48-14.sql', '2025-05-06 16:11:44'),
(156, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 10:45:43'),
(158, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_16-06-59.sql', '2025-05-07 10:47:24'),
(159, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-06_16-18-17.sql', '2025-05-07 10:47:26');

INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
(160, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 11:30:19'),
(161, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 13:41:38'),
(162, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 14:39:00'),
(163, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 16:13:07'),
(164, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-07 16:31:04'),
(165, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-08 08:28:46'),
(166, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-09 08:13:46'),
(168, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-09 08:47:32'),
(169, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-09 08:57:48'),
(170, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: uploaded_2025-05-09_08-18-51.sql', '2025-05-09 08:57:59'),
(171, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-09_08-58-00.sql', '2025-05-09 08:58:00'),
(172, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-09_08-58-00.sql', '2025-05-09 08:58:04'),
(173, 1, 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded and validated backup file: uploaded_2025-05-09_08-59-59.sql', '2025-05-09 08:59:59'),
(23, 6, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged in successfully', '2025-05-03 09:51:24'),
(24, 6, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged out', '2025-05-03 09:51:55');
========
(42, 1, 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_19-21-53.sql', '2025-05-03 19:22:01');
>>>>>>>> 213e80d030f679110bdbf8171d53f7dfc84f749c:public/backups/uploaded_2025-05-03_19-24-15.sql

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
-- Indexes for table `notice_status`
--
ALTER TABLE `notice_status`
  ADD PRIMARY KEY (`notice_stat_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `establishment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notice_status`
--
ALTER TABLE `notice_status`
  MODIFY `notice_stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `logs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

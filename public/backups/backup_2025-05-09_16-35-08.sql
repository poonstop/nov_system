-- Database Backup for nov_system7
-- Generated: 2025-05-09 16:35:08

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET CHARACTER_SET_CLIENT=utf8mb4;
SET CHARACTER_SET_RESULTS=utf8mb4;
SET NAMES utf8mb4;

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `ulvl` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `addresses`
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `establishment_id` int(11) NOT NULL,
  `street` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `establishments`
DROP TABLE IF EXISTS `establishments`;
CREATE TABLE `establishments` (
  `establishment_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `date_updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`establishment_id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(20, 20, 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg'),
(21, 21, 'sdf', 'sdfs', 'dsfs', 'falls', 'swallow'),
(22, 22, 'asd', 'df', 'fdsf', 'dfsdfs', 'sdfs'),
(23, 23, 'far away', 'far', 'everywhere', 'anywhere', 'somewhere'),
(24, 24, 'ert', 'wrt', 'wret', 'wrtwe', 'retert'),
(25, 25, 'WER', 'WER', 'WER', 'WE', 'EW'),
(26, 26, 'dfgdgd', 'dfgd', 'dgdf', 'dgd', 'dfgd');

-- Data for table `inventory`
-- 7 rows
INSERT INTO `inventory` (`inventory_id`, `establishment_id`, `product_name`, `sealed`, `withdrawn`, `description`, `price`, `pieces`, `dao_violation`, `other_violation`, `inv_remarks`, `date_created`) VALUES
(1, 0, 'dsfsdf', 0, 0, '0', 11.00, 22, 1, 0, '22awdsa', '2025-04-28 01:06:18'),
(2, 0, 'dfsdfs', 1, 0, '0', 12.00, 0, 1, 0, 'dwq', '2025-04-28 01:07:55'),
(3, 5, 'toys', 0, 1, '0', 1223.00, 123, 0, 1, 'sfsdfs', '2025-04-28 01:22:28'),
(4, 6, 'sdgfdfg', 1, 1, '0', 12.00, 12, 1, 1, 'fsdfsd', '2025-04-28 01:24:08'),
(5, 6, 'dsdfsd', 1, 1, '0', 12.00, 12, 1, 0, 'sfdsfs', '2025-04-28 01:24:08'),
(6, 7, 'werw', 1, 0, '0', 12.00, 12, 1, 0, '12qweqw', '2025-04-28 01:36:50'),
(7, 8, 'sdas', 1, 1, '0', 12.00, 12, 1, 0, 'asdasda', '2025-04-28 01:38:55');

-- Data for table `notice_status`
-- 26 rows
INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
(1, 0, 'Received', 'asda', 'asdas', '2025-04-28 09:06:07', '', '2025-04-30 17:06:18', '2025-04-28 01:06:18'),
(2, 0, 'Received', 'adas', 'sadas', '2025-04-28 09:07:48', '', '2025-04-30 17:07:55', '2025-04-28 01:07:55'),
(3, 0, 'Refused', '', '', '2025-04-28 09:09:00', 'me', '2025-04-30 17:09:04', '2025-04-28 01:09:04'),
(4, 4, 'Received', 'rgr', 'fsdfds', '2025-04-28 09:19:29', '', '2025-04-30 17:19:38', '2025-04-28 01:19:38'),
(5, 5, 'Received', 'sdfsd', 'missionary', '2025-04-28 09:22:20', '', '2025-04-30 17:22:28', '2025-04-28 01:22:28'),
(6, 6, 'Refused', '', '', '2025-04-28 09:24:02', 'shi', '2025-04-30 17:24:08', '2025-04-28 01:24:08'),
(7, 7, 'Received', 'asdas', 'werwr', '2025-04-28 08:59:31', '', '2025-04-30 17:36:50', '2025-04-28 01:36:50'),
(8, 8, 'Refused', '', '', '2025-04-28 09:38:49', 'you', '2025-04-30 17:38:55', '2025-04-28 01:38:55'),
(9, 9, 'Refused', '', '', '2025-04-28 15:34:45', 'Aym Yu', '2025-04-30 23:35:02', '2025-04-28 07:35:02'),
(10, 10, 'Refused', '', '', '2025-04-02 08:36:00', 'Naruto', '2025-04-30 23:36:55', '2025-04-28 07:36:55'),
(11, 11, 'Received', 'zxcz', 'werwr', '2025-04-28 15:47:12', '', '2025-04-30 23:47:19', '2025-04-28 07:47:19'),
(12, 12, 'Received', 'asdas', 'sd', '2025-04-25 16:04:00', '', '2025-05-01 00:04:56', '2025-04-28 08:04:56'),
(13, 13, 'Received', 'Ah', 'Supervisor', '2025-04-28 16:11:48', '', '2025-05-01 00:12:32', '2025-04-28 08:12:32'),
(14, 14, 'Received', 'me', 'myself', '2025-04-28 16:44:23', '', '2025-05-01 00:44:33', '2025-04-28 08:44:33'),
(15, 15, 'Refused', '', '', '2025-04-29 10:15:20', 'aym yu', '2025-05-01 18:15:28', '2025-04-29 02:15:28'),
(16, 16, 'Received', 'dasf', 'adf', '2025-04-29 10:23:28', '', '2025-05-01 18:23:34', '2025-04-29 02:23:34'),
(17, 17, 'Received', 'asdf', 'afdas', '2025-04-29 10:34:09', '', '2025-05-01 18:34:16', '2025-04-29 02:34:16'),
(18, 18, 'Refused', '', '', '2025-04-21 14:03:00', 'ed', '2025-05-01 22:04:11', '2025-04-29 06:04:11'),
(19, 19, 'Received', 'Cleff', 'O5-13', '2025-04-29 15:50:59', '', '2025-05-01 23:52:43', '2025-04-29 15:52:43'),
(20, 20, 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-04 23:56:50', '2025-05-02 15:56:50'),
(21, 21, 'Refused', '', '', '2025-05-02 11:34:00', 'Aym Yu', '2025-05-08 11:36:21', '2025-05-07 11:36:21'),
(22, 22, 'Received', 'rgr', 'asda', '2025-05-05 13:55:00', '', '2025-05-08 13:54:10', '2025-05-07 13:54:10'),
(23, 23, 'Refused', '', '', '2025-05-05 14:05:00', 'Till', '2025-05-08 13:58:34', '2025-05-07 13:58:34'),
(24, 24, 'Refused', '', '', '2025-05-05 14:50:00', 'dfgd', '2025-05-08 14:41:36', '2025-05-07 14:41:36'),
(25, 25, 'Refused', '', '', '2025-05-08 08:13:00', 'AYM YU', '2025-05-10 08:14:50', '2025-05-09 08:14:50'),
(26, 26, 'Received', 'dfg', 'dfgd', '2025-05-07 08:15:00', '', '2025-05-10 08:15:45', '2025-05-09 08:15:45');

-- Data for table `user_logs`
-- 3 rows
INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
(198, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-09 16:34:58'),
(199, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-09_15-55-08.sql', '2025-05-09 16:35:06'),
(200, 1, 'BACKUP_DELETED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Deleted backup file: backup_2025-05-09_16-15-07.sql', '2025-05-09 16:35:07');


-- Set proper delimiter for routines
DELIMITER $$


-- Reset delimiter
DELIMITER ;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

-- End of backup

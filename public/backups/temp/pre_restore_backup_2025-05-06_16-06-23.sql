-- Database Backup for nov_system7
-- Generated: 2025-05-06 16:06:23

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
-- 5 rows
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
(1, 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', '', 'admin', 'active', ''),
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', '', '', ''),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani');

-- Data for table `notice_status`
-- 20 rows
INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
(1, 0, 'Received', 'asda', 'asdas', '2025-04-28 09:06:07', '', '2025-04-28 17:06:18', '2025-04-28 01:06:18'),
(2, 0, 'Received', 'adas', 'sadas', '2025-04-28 09:07:48', '', '2025-04-28 17:07:55', '2025-04-28 01:07:55'),
(3, 0, 'Refused', '', '', '2025-04-28 09:09:00', 'me', '2025-04-28 17:09:04', '2025-04-28 01:09:04'),
(4, 4, 'Received', 'rgr', 'fsdfds', '2025-04-28 09:19:29', '', '2025-04-28 17:19:38', '2025-04-28 01:19:38'),
(5, 5, 'Received', 'sdfsd', 'missionary', '2025-04-28 09:22:20', '', '2025-04-28 17:22:28', '2025-04-28 01:22:28'),
(6, 6, 'Refused', '', '', '2025-04-28 09:24:02', 'shi', '2025-04-28 17:24:08', '2025-04-28 01:24:08'),
(7, 7, 'Received', 'asdas', 'werwr', '2025-04-28 08:59:31', '', '2025-04-28 17:36:50', '2025-04-28 01:36:50'),
(8, 8, 'Refused', '', '', '2025-04-28 09:38:49', 'you', '2025-04-28 17:38:55', '2025-04-28 01:38:55'),
(9, 9, 'Refused', '', '', '2025-04-28 15:34:45', 'Aym Yu', '2025-04-28 23:35:02', '2025-04-28 07:35:02'),
(10, 10, 'Refused', '', '', '2025-04-02 08:36:00', 'Naruto', '2025-04-28 23:36:55', '2025-04-28 07:36:55'),
(11, 11, 'Received', 'zxcz', 'werwr', '2025-04-28 15:47:12', '', '2025-04-28 23:47:19', '2025-04-28 07:47:19'),
(12, 12, 'Received', 'asdas', 'sd', '2025-04-25 16:04:00', '', '2025-04-29 00:04:56', '2025-04-28 08:04:56'),
(13, 13, 'Received', 'Ah', 'Supervisor', '2025-04-28 16:11:48', '', '2025-04-29 00:12:32', '2025-04-28 08:12:32'),
(14, 14, 'Received', 'me', 'myself', '2025-04-28 16:44:23', '', '2025-04-29 00:44:33', '2025-04-28 08:44:33'),
(15, 15, 'Refused', '', '', '2025-04-29 10:15:20', 'aym yu', '2025-04-29 18:15:28', '2025-04-29 02:15:28'),
(16, 16, 'Received', 'dasf', 'adf', '2025-04-29 10:23:28', '', '2025-04-29 18:23:34', '2025-04-29 02:23:34'),
(17, 17, 'Received', 'asdf', 'afdas', '2025-04-29 10:34:09', '', '2025-04-29 18:34:16', '2025-04-29 02:34:16'),
(18, 18, 'Refused', '', '', '2025-04-21 14:03:00', 'ed', '2025-04-29 22:04:11', '2025-04-29 06:04:11'),
(19, 19, 'Received', 'Cleff', 'O5-13', '2025-04-29 15:50:59', '', '2025-04-29 23:52:43', '2025-04-29 15:52:43'),
(20, 20, 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-02 23:56:50', '2025-05-02 15:56:50');

-- Data for table `user_logs`
-- 37 rows
INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
(32, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-07.sql', '2025-05-03 19:15:07'),
(33, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-12.sql', '2025-05-03 19:15:12'),
(34, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-13.sql', '2025-05-03 19:15:13'),
(35, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-26.sql', '2025-05-03 19:15:26'),
(36, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-37.sql', '2025-05-03 19:16:37'),
(37, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-39.sql', '2025-05-03 19:16:39'),
(38, NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-53.sql', '2025-05-03 19:16:53'),
(25, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 09:53:02'),
(26, 1, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-03 10:20:37'),
(27, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 10:21:46'),
(28, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 14:20:28'),
(29, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:00:42'),
(30, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:11:25'),
(31, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 19:10:06'),
(39, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-07.sql', '2025-05-03 19:21:07'),
(40, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-49.sql', '2025-05-03 19:21:49'),
(41, 1, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-53.sql', '2025-05-03 19:21:53'),
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
(141, 1, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-06 16:06:14'),
(23, 6, 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged in successfully', '2025-05-03 09:51:24'),
(24, 6, 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged out', '2025-05-03 09:51:55');


-- Set proper delimiter for routines
DELIMITER $$


-- Reset delimiter
DELIMITER ;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

-- End of backup

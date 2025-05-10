-- Database Backup for nov_system7
-- Generated: 2025-05-03 21:08:54

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- Data for table `addresses`
-- 20 rows
START TRANSACTION;
INSERT INTO `addresses` (`address_id`, `establishment_id`, `street`, `barangay`, `municipality`, `province`, `region`) VALUES
('1', '0', 'asdas', 'asda', 'asda', 'dsada', 'asdasa'),
('2', '0', 'sdf', 'sdf', 'fdsf', 'dfsdfs', 'sdfs'),
('3', '0', 'asd', 'asd', 'asd', 'asdasd', 'sdas'),
('4', '4', 'sdf', 'df', 'dsf', 'leaf', 'konoha'),
('5', '5', 'dfdsfs', 'fsfsfs', 'fsfs', 'dfsfsdfds', 'sdfsd'),
('6', '6', 'fgdfgdf', 'gdfgd', 'gdfgdgfd', 'fgdfgfd', 'dfgdgd'),
('7', '7', 'wer', 'ewr', 'werw', 'ewwe', 'werww'),
('8', '8', 'delussion', 'land', 'believe', 'make', '2'),
('9', '9', 'sdfsdfs', 'fsdfsdf', 'fsdfsdfsd', 'fdsfdsfsd', 'dsvdsvsd'),
('10', '10', 'vxcvx', 'cxvxvzx', 'vzxvxcv', 'vzxcvzxcvczx', 'xzcvzx'),
('11', '11', 'dsfsd', 'sdfs', 'dfsdf', 'fsdfsdfsd', 'dfsdfsd'),
('12', '12', 'fasfaf', 'asfa', 'fasfas', 'fafasfas', 'sfas'),
('13', '13', 'dsf', 'dsf', 'sdf', 'dfs', 'asd'),
('14', '14', 'dsfsdfsdfsdfsd', 'fsdfsdf', 'sdfsdf', 'fsdfsdf', 'dfsdfsdfsd'),
('15', '15', 'sdgsd', 'dsgds', 'gsgds', 'sdgs', 'gsgsgsdg'),
('16', '16', 'asf', 'asf', 'asdf', 'asfd', 'asdfs'),
('17', '17', 'adf', 'asdf', 'sadf', 'asdf', 'adfs'),
('18', '18', 'xbfxb', 'Quirino', 'Tagudin', 'ilocos sur', '1'),
('19', '19', 'From Reality', 'Away', 'Far', 'far', 'Somewhere'),
('20', '20', 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg');
COMMIT;

-- Data for table `establishments`
-- 20 rows
START TRANSACTION;
INSERT INTO `establishments` (`establishment_id`, `name`, `owner_representative`, `nature`, `products`, `violations`, `notice_status`, `remarks`, `nov_files`, `created_at`, `issued_by`, `issued_datetime`, `num_violations`, `date_created`, `date_updated`) VALUES
('1', 'asad', 'Mister Krabs', 'Service and Repair', 'asdasda', 'No PS/ICC Mark', 'Received', 'asd', 'asad_1745802355.txt', '2025-04-28 09:06:18', 'asda', '2025-04-28 09:06:07', NULL, '2025-04-28 01:06:18', '2025-04-28 01:06:18'),
('2', 'Meliodas', 'humphry', 'Supermarket/Grocery/Convenience Store', 'sdfs', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Received', 'dfsdfs', 'Meliodas_1745802459.txt', '2025-04-28 09:07:55', 'adas', '2025-04-28 09:07:48', NULL, '2025-04-28 01:07:55', '2025-04-28 01:07:55'),
('3', 'asdaa', 'sdasd', 'Service and Repair', 'asda', 'Invalid/suspended or cancelled BPS license', 'Refused', '', 'asdaa_1745802539.txt', '2025-04-28 09:09:04', '', '2025-04-28 09:09:00', NULL, '2025-04-28 01:09:04', '2025-04-28 01:09:04'),
('4', 'ichiraku ramen', 'naruto', 'Service and Repair', 'sdfs', 'No Manufacturer&#039;s Address', 'Received', '', 'ichirakuramen_1745803167.txt', '2025-04-28 09:19:38', 'rgr', '2025-04-28 09:19:29', NULL, '2025-04-28 01:19:38', '2025-04-28 01:19:38'),
('5', 'dsfs', 'dfsdf', 'Retail/Wholesaler', 'toys', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Received', 'asasadsds', 'dsfs_1745803324.txt', '2025-04-28 09:22:28', 'sdfsd', '2025-04-28 09:22:20', NULL, '2025-04-28 01:22:28', '2025-04-28 01:22:28'),
('6', 'gdfgdf', 'fdgddf', 'Service and Repair', 'dfgdfg', 'No PS/ICC Mark, No Manufacturer&#039;s Name', 'Refused', 'not good enough', 'gdfgdf_1745803418.txt', '2025-04-28 09:24:08', '', '2025-04-28 09:24:02', NULL, '2025-04-28 01:24:08', '2025-04-28 01:24:08'),
('7', 'erew', 'saadsd', 'Supermarket/Grocery/Convenience Store', 'ewrw', 'No PS/ICC Mark', 'Received', 'erwer', 'erew_1745801962.txt', '2025-04-28 09:36:50', 'asdas', '2025-04-28 08:59:31', NULL, '2025-04-28 01:36:50', '2025-04-28 01:36:50'),
('8', 'Pete&#039;s Pizzeria and Abortion Clinic', 'Pete', 'food', 'pizza sauce', 'No Manufacturer&#039;s Name, No Product Contents/Ingredients', 'Refused', 'disgusting', 'Pete039sPizzeriaandAbortionClinic_1745804315.txt', '2025-04-28 09:38:55', '', '2025-04-28 09:38:49', NULL, '2025-04-28 01:38:55', '2025-04-28 01:38:55'),
('9', 'Boar&#039;s Hut', 'Meliodas', 'Hardware', 'Ale', 'No Manufacturer&#039;s Name, No Batch Number/Lot Code', 'Refused', 'ssssssss', 'Boar039sHut_1745825682.txt', '2025-04-28 15:35:02', '', '2025-04-28 15:34:45', NULL, '2025-04-28 07:35:02', '2025-04-28 07:35:02'),
('10', 'cvzxcv', 'cvzxv', 'Service and Repair', 'zxcv', 'No Manufacturer&#039;s Name, No Country of Origin', 'Refused', '', 'cvzxcv_1745825782.txt', '2025-04-28 15:36:55', '', '2025-04-02 08:36:00', NULL, '2025-04-28 07:36:55', '2025-04-28 07:36:55'),
('11', 'Casita\'s mansion', 'Pepa', 'Hardware', 'sdf', 'No PS/ICC Mark, No Manufacturer\'s Address', 'Received', '', 'Casitasmansion_1745826429.txt', '2025-04-28 15:47:19', 'zxcz', '2025-04-28 15:47:12', NULL, '2025-04-28 07:47:19', '2025-04-28 07:47:19'),
('12', 'dota shop', 'shopkeeper', 'Retail/Wholesaler', 'Dangerous Items', 'No Manufacturer\'s Address, No Accreditation Certification', 'Received', '', 'dotashop_1745827482.txt', '2025-04-28 16:04:56', 'asdas', '2025-04-25 16:04:00', NULL, '2025-04-28 08:04:56', '2025-04-28 08:04:56'),
('13', 'fsdfs', 'meliodafu', 'Hardware', 'dsfsdf', 'Expired Accreditation Certificate, Freight Business with Failure to Display valid copy of Accreditation Certificate in conspicuous place in the establishment.', 'Received', 'dsfdsf', 'fsdfs_1745827904.txt', '2025-04-28 16:12:32', 'Ah', '2025-04-28 16:11:48', NULL, '2025-04-28 08:12:32', '2025-04-28 08:12:32'),
('14', 'fsdfsdfsdf', 'dfsdfs', 'Manufacturing', 'dsfsdf', 'Price grossly in excess of its/their true worth, Engaging in business using trade name on signages and/or documents without prior registration, Failure to Display Business Name Certificate, Conducting Sales Promotion without Sales Promotion Permit.', 'Received', 'fgdfgfdgf', 'fsdfsdfsdf_1745829861.txt', '2025-04-28 16:44:33', 'me', '2025-04-28 16:44:23', NULL, '2025-04-28 08:44:33', '2025-04-28 08:44:33'),
('15', 'gdsgsgsdgs', 'gasgsd', 'Supermarket/Grocery/Convenience Store', 'sdg', 'Conducting Sales Promotion without Sales Promotion Permit.', 'Refused', 'sdfsd', 'gdsgsgsdgs_1745892917.txt', '2025-04-29 10:15:28', '', '2025-04-29 10:15:20', NULL, '2025-04-29 02:15:28', '2025-04-29 02:15:28'),
('16', 'dsfs', 'Boar Hut', 'Hardware', 'adf', '', 'Received', '', 'dsfs_1745893407.txt', '2025-04-29 10:23:34', 'dasf', '2025-04-29 10:23:28', NULL, '2025-04-29 02:23:34', '2025-04-29 02:23:34'),
('17', 'sadfs', 'dfsda', 'Supermarket/Grocery/Convenience Store', 'adfas', 'Conducting Sales Promotion without Sales Promotion Permit.', 'Received', '', 'sadfs_1745894044.txt', '2025-04-29 10:34:16', 'asdf', '2025-04-29 10:34:09', NULL, '2025-04-29 02:34:16', '2025-04-29 02:34:16'),
('18', 'cgd', 'tdh', 'Manufacturing', 'toys', 'Failure to Display Business Name Certificate', 'Refused', '', 'cgd_1745906632.txt', '2025-04-29 14:04:11', '', '2025-04-21 14:03:00', NULL, '2025-04-29 06:04:11', '2025-04-29 06:04:11'),
('19', 'Serpent\'s Library', 'LS', 'Retail/Wholesaler', 'Books', 'No Manufacturer\'s Name', 'Received', 'breaks reality', 'SerpentsLibrary_1745913163.txt', '2025-04-29 15:52:43', 'Cleff', '2025-04-29 15:50:59', NULL, '2025-04-29 15:52:43', '2025-04-29 15:52:43'),
('20', 'dfgdfg', 'dgsdg', 'Service and Repair', 'dfgdf', 'Price is beyond the price cieling', 'Refused', '', 'dfgdfg_1746172610.txt', '2025-05-02 15:56:50', '', '2025-05-01 15:56:00', NULL, '2025-05-02 15:56:50', '2025-05-02 15:56:50');
COMMIT;

-- Data for table `inventory`
-- 7 rows
START TRANSACTION;
INSERT INTO `inventory` (`inventory_id`, `establishment_id`, `product_name`, `sealed`, `withdrawn`, `description`, `price`, `pieces`, `dao_violation`, `other_violation`, `inv_remarks`, `date_created`) VALUES
('1', '0', 'dsfsdf', '0', '0', '0', '11.00', '22', '1', '0', '22awdsa', '2025-04-28 01:06:18'),
('2', '0', 'dfsdfs', '1', '0', '0', '12.00', '0', '1', '0', 'dwq', '2025-04-28 01:07:55'),
('3', '5', 'toys', '0', '1', '0', '1223.00', '123', '0', '1', 'sfsdfs', '2025-04-28 01:22:28'),
('4', '6', 'sdgfdfg', '1', '1', '0', '12.00', '12', '1', '1', 'fsdfsd', '2025-04-28 01:24:08'),
('5', '6', 'dsdfsd', '1', '1', '0', '12.00', '12', '1', '0', 'sfdsfs', '2025-04-28 01:24:08'),
('6', '7', 'werw', '1', '0', '0', '12.00', '12', '1', '0', '12qweqw', '2025-04-28 01:36:50'),
('7', '8', 'sdas', '1', '1', '0', '12.00', '12', '1', '0', 'asdasda', '2025-04-28 01:38:55');
COMMIT;

-- Data for table `notice_status`
-- 20 rows
START TRANSACTION;
INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
('1', '0', 'Received', 'asda', 'asdas', '2025-04-28 09:06:07', '', '2025-04-28 09:06:18', '2025-04-28 01:06:18'),
('2', '0', 'Received', 'adas', 'sadas', '2025-04-28 09:07:48', '', '2025-04-28 09:07:55', '2025-04-28 01:07:55'),
('3', '0', 'Refused', '', '', '2025-04-28 09:09:00', 'me', '2025-04-28 09:09:04', '2025-04-28 01:09:04'),
('4', '4', 'Received', 'rgr', 'fsdfds', '2025-04-28 09:19:29', '', '2025-04-28 09:19:38', '2025-04-28 01:19:38'),
('5', '5', 'Received', 'sdfsd', 'missionary', '2025-04-28 09:22:20', '', '2025-04-28 09:22:28', '2025-04-28 01:22:28'),
('6', '6', 'Refused', '', '', '2025-04-28 09:24:02', 'shi', '2025-04-28 09:24:08', '2025-04-28 01:24:08'),
('7', '7', 'Received', 'asdas', 'werwr', '2025-04-28 08:59:31', '', '2025-04-28 09:36:50', '2025-04-28 01:36:50'),
('8', '8', 'Refused', '', '', '2025-04-28 09:38:49', 'you', '2025-04-28 09:38:55', '2025-04-28 01:38:55'),
('9', '9', 'Refused', '', '', '2025-04-28 15:34:45', 'Aym Yu', '2025-04-28 15:35:02', '2025-04-28 07:35:02'),
('10', '10', 'Refused', '', '', '2025-04-02 08:36:00', 'Naruto', '2025-04-28 15:36:55', '2025-04-28 07:36:55'),
('11', '11', 'Received', 'zxcz', 'werwr', '2025-04-28 15:47:12', '', '2025-04-28 15:47:19', '2025-04-28 07:47:19'),
('12', '12', 'Received', 'asdas', 'sd', '2025-04-25 16:04:00', '', '2025-04-28 16:04:56', '2025-04-28 08:04:56'),
('13', '13', 'Received', 'Ah', 'Supervisor', '2025-04-28 16:11:48', '', '2025-04-28 16:12:32', '2025-04-28 08:12:32'),
('14', '14', 'Received', 'me', 'myself', '2025-04-28 16:44:23', '', '2025-04-28 16:44:33', '2025-04-28 08:44:33'),
('15', '15', 'Refused', '', '', '2025-04-29 10:15:20', 'aym yu', '2025-04-29 10:15:28', '2025-04-29 02:15:28'),
('16', '16', 'Received', 'dasf', 'adf', '2025-04-29 10:23:28', '', '2025-04-29 10:23:34', '2025-04-29 02:23:34'),
('17', '17', 'Received', 'asdf', 'afdas', '2025-04-29 10:34:09', '', '2025-04-29 10:34:16', '2025-04-29 02:34:16'),
('18', '18', 'Refused', '', '', '2025-04-21 14:03:00', 'ed', '2025-04-29 14:04:11', '2025-04-29 06:04:11'),
('19', '19', 'Received', 'Cleff', 'O5-13', '2025-04-29 15:50:59', '', '2025-04-29 15:52:43', '2025-04-29 15:52:43'),
('20', '20', 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-02 15:56:50', '2025-05-02 15:56:50');
COMMIT;

-- Data for table `user_logs`
-- 31 rows
START TRANSACTION;
INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
('23', '6', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged in successfully', '2025-05-03 09:51:24'),
('24', '6', 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User cleff (anonymous) logged out', '2025-05-03 09:51:55'),
('25', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 09:53:02'),
('26', '1', 'Logout', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged out', '2025-05-03 10:20:37'),
('27', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 10:21:46'),
('28', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 14:20:28'),
('29', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:00:42'),
('30', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 15:11:25'),
('31', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 19:10:06'),
('32', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-07.sql', '2025-05-03 19:15:07'),
('33', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-12.sql', '2025-05-03 19:15:12'),
('34', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-13.sql', '2025-05-03 19:15:13'),
('35', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-15-26.sql', '2025-05-03 19:15:26'),
('36', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-37.sql', '2025-05-03 19:16:37'),
('37', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-39.sql', '2025-05-03 19:16:39'),
('38', NULL, 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-16-53.sql', '2025-05-03 19:16:53'),
('39', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-07.sql', '2025-05-03 19:21:07'),
('40', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-49.sql', '2025-05-03 19:21:49'),
('41', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-21-53.sql', '2025-05-03 19:21:53'),
('42', '1', 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_19-21-53.sql', '2025-05-03 19:22:01'),
('43', '1', 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-33-33.sql', '2025-05-03 19:33:33'),
('44', '1', 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-33-37.sql', '2025-05-03 19:33:37'),
('45', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_19-33-39.sql', '2025-05-03 19:33:39'),
('46', '1', 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_19-33-39.sql', '2025-05-03 19:33:48'),
('47', '1', 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_19-34-43.sql', '2025-05-03 19:34:43'),
('48', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 20:55:16'),
('49', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_20-55-38.sql', '2025-05-03 20:55:38'),
('50', '1', 'BACKUP_DOWNLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Downloaded backup file: backup_2025-05-03_20-55-38.sql', '2025-05-03 20:55:50'),
('51', '1', 'BACKUP_UPLOADED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Uploaded backup file: uploaded_2025-05-03_20-56-03.sql', '2025-05-03 20:56:03'),
('52', '1', 'BACKUP_CREATED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Created database backup (PHP method): backup_2025-05-03_21-08-39.sql', '2025-05-03 21:08:39'),
('53', '1', 'Login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'User admin () logged in successfully', '2025-05-03 21:08:46');
COMMIT;

-- Data for table `users`
-- 6 rows
START TRANSACTION;
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
('1', 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', '', 'admin', 'active', ''),
('2', 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', '', '', ''),
('3', 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
('4', 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
('5', '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani.mo'),
('6', 'cleff', '$2y$10$M.xmt1vfu2GE7bUUrzDmKOh08ShUGURUkXX1DblSJ9rMjZJpMnw0K', 'anonymous', 'inspector', 'active', 'suevos@tempmailto.org');
COMMIT;

SET FOREIGN_KEY_CHECKS=1;

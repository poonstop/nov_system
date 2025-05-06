-- Database Backup for nov_system7
-- Generated: 2025-05-06 08:30:49

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
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- 1 rows
START TRANSACTION;
INSERT INTO `addresses` (`address_id`, `establishment_id`, `street`, `barangay`, `municipality`, `province`, `region`) VALUES
('20', '20', 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg');
COMMIT;

-- Data for table `establishments`
-- 1 rows
START TRANSACTION;
INSERT INTO `establishments` (`establishment_id`, `name`, `owner_representative`, `nature`, `products`, `violations`, `notice_status`, `remarks`, `nov_files`, `created_at`, `issued_by`, `issued_datetime`, `num_violations`, `date_created`, `date_updated`) VALUES
('20', 'dfgdfg', 'dgsdg', 'Service and Repair', 'dfgdf', 'Price is beyond the price cieling', 'Refused', '', 'dfgdfg_1746172610.txt', '2025-05-03 15:56:50', '', '2025-05-01 15:56:00', NULL, '2025-05-02 15:56:50', '2025-05-02 15:56:50');
COMMIT;

-- Data for table `inventory`
-- 1 rows
START TRANSACTION;
INSERT INTO `inventory` (`inventory_id`, `establishment_id`, `product_name`, `sealed`, `withdrawn`, `description`, `price`, `pieces`, `dao_violation`, `other_violation`, `inv_remarks`, `date_created`) VALUES
('7', '8', 'sdas', '1', '1', '0', '12.00', '12', '1', '0', 'asdasda', '2025-04-28 01:38:55');
COMMIT;

-- Data for table `notice_status`
-- 1 rows
START TRANSACTION;
INSERT INTO `notice_status` (`notice_stat_id`, `establishment_id`, `status`, `issued_by`, `position`, `issued_datetime`, `witnessed_by`, `created_at`, `updated_at`) VALUES
('20', '20', 'Refused', '', '', '2025-05-01 15:56:00', 'dfgdf', '2025-05-03 15:56:50', '2025-05-02 15:56:50');
COMMIT;

-- Data for table `user_logs`
-- 2 rows
START TRANSACTION;
INSERT INTO `user_logs` (`logs_id`, `user_id`, `action`, `user_agent`, `details`, `timestamp`) VALUES
('111', '1', 'BACKUP_RESTORED', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Restored database: backup_2025-05-06_08-29-11.sql', '2025-05-06 00:30:47');
COMMIT;

-- Data for table `users`
-- 1 rows
START TRANSACTION;
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
('5', '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani');
COMMIT;

SET FOREIGN_KEY_CHECKS=1;

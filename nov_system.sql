-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 02:51 PM
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
-- Database: `nov_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `street` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `full_address` text GENERATED ALWAYS AS (concat(`street`,', ',`barangay`,', ',`municipality`,', ',`province`,', ',`region`)) STORED COMMENT 'Auto-generated complete address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
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

INSERT INTO `establishments` (`id`, `name`, `address`, `owner_representative`, `nature`, `products`, `violations`, `notice_status`, `remarks`, `nov_files`, `created_at`, `issued_by`, `issued_datetime`, `num_violations`, `date_created`, `date_updated`) VALUES
(1, 'Abc Company', '123 Main Street La Union', 'Not Specified', 'Manufacturing', '[{\"id\":\"\",\"product_name\":\"vapes\",\"price\":\"0.00\",\"quantity\":\"0\",\"sealed\":0,\"withdrawn\":0}]', 'Price Tag Violations', NULL, 'urgent pls', 'abccompany_1742967191.txt', '2025-03-26 05:33:11', NULL, NULL, '1', '2025-04-15 10:14:47', '2025-04-17 21:15:40'),
(4, 'topwood', 'agoo la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'pending', 'topwood_1742971309.txt', '2025-03-26 06:41:49', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(6, 'vape shop', 'aringay, la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark', NULL, 'pls confirm', 'vapeshop_1742972686.txt', '2025-03-26 07:04:46', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(7, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742972751.txt', '2025-03-26 07:05:51', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(8, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742973191.txt', '2025-03-26 07:13:11', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(9, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742973214.txt', '2025-03-26 07:13:34', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(10, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742973802.txt', '2025-03-26 07:23:22', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(11, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742973824.txt', '2025-03-26 07:23:44', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(12, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742974061.txt', '2025-03-26 07:27:41', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(13, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', NULL, 'irgent', 'vapeshop_1742974096.txt', '2025-03-26 07:28:16', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(14, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'unreg', 'hjshop_1742974390.txt', '2025-03-26 07:33:10', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(15, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'unreg', 'hjshop_1742974651.txt', '2025-03-26 07:37:31', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(16, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'unreg', 'hjshop_1742974684.txt', '2025-03-26 07:38:04', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(17, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'unreg', 'hjshop_1742975155.txt', '2025-03-26 07:45:55', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(18, 'Lebron shoe shop', 'san fernando city la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'investigate', 'Lebronshoeshop_1742976182.txt', '2025-03-26 08:03:02', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(19, 'hj shop', 'aringay, la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, '', 'hjshop_1742978484.txt', '2025-03-26 08:41:24', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(20, 'The Bar', 'sta lucia, aringay, la union', NULL, 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation, Improper Labeling, Price Tag Violations', NULL, 'urgent', 'TheBar_1742978626.txt', '2025-03-26 08:43:46', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(21, 'the shoe lab', 'aringay la union', 'joshwil jason', 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'urgent', 'theshoelab_1742979638.txt', '2025-03-26 09:00:38', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(22, 'the beatles', 'san fernando city la union', 'john lennon', 'Manufacturing', 'guitar', 'No PS/ICC Mark, Price Tag Violations', NULL, 'arrest', 'thebeatles_1743034900.txt', '2025-03-27 00:21:40', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(23, 'lebron bron shop', 'los angeles pampanga', 'lebron jarmes', 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'URGENT!!!', 'lebronbronshop_1743038720.txt', '2025-03-27 01:25:20', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(24, 'usa shopping center', 'angeles pampanga', 'barack obams', 'Manufacturing', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'URGENT!!!!', 'usashoppingcenter_1743039054.txt', '2025-03-27 01:30:54', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(25, 'jake company', 'san fernando city la union', 'jake paul', 'youtube', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'confirm', 'jakecompany_1743042966.txt', '2025-03-27 02:36:06', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47'),
(26, 'vsvjnksnvjsn', 'vjknssjkdsv', 'ncjancksc', 'Manufacturing', 'vdsvsdvsdv', 'No PS/ICC Mark, Invalid/Expired Accreditation', NULL, 'svsdvsdv', 'vsvjnksnvjsn_1743043042.txt', '2025-03-27 02:37:22', NULL, NULL, NULL, '2025-04-15 10:14:47', '2025-04-15 10:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sealed` tinyint(1) DEFAULT 0,
  `withdrawn` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `pieces` int(11) DEFAULT NULL,
  `dao_violation` tinyint(1) DEFAULT 0,
  `other_violation` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notice_status`
--

CREATE TABLE `notice_status` (
  `notice_stat_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `issued_datetime` datetime DEFAULT NULL,
  `witnessed_by` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'inactive', 'nanimo@nani.mo');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `timestamp`) VALUES
(1, 1, 'logout', '2025-03-25 14:40:20'),
(2, 1, 'logout', '2025-03-25 14:46:48'),
(3, 1, 'logout', '2025-03-25 15:00:59'),
(4, 1, 'logout', '2025-03-25 15:02:05'),
(5, 1, 'logout', '2025-03-25 15:05:29'),
(6, 1, 'logout', '2025-03-25 15:09:30'),
(7, 1, 'logout', '2025-03-25 15:18:22'),
(8, 1, 'logout', '2025-03-25 16:20:05'),
(9, 1, 'logout', '2025-03-26 08:19:28'),
(10, 1, 'logout', '2025-03-26 09:06:03'),
(11, 1, 'logout', '2025-03-26 09:19:33'),
(12, 4, 'logout', '2025-03-26 16:16:12'),
(13, 1, 'logout', '2025-03-26 16:20:13'),
(14, 1, 'logout', '2025-03-26 16:20:44'),
(15, 1, 'logout', '2025-03-26 16:21:45'),
(16, 1, 'logout', '2025-03-26 16:22:20'),
(17, 1, 'logout', '2025-03-27 09:20:33'),
(18, 1, 'logout', '2025-03-27 09:23:57'),
(19, 1, 'logout', '2025-03-27 09:29:12'),
(20, 1, 'logout', '2025-03-27 09:31:23'),
(21, 1, 'logout', '2025-03-27 10:16:18'),
(22, 1, 'logout', '2025-03-27 10:59:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_location` (`region`,`province`,`municipality`),
  ADD KEY `idx_barangay` (`barangay`);

--
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_inventory_establishment` (`id`);

--
-- Indexes for table `notice_status`
--
ALTER TABLE `notice_status`
  ADD PRIMARY KEY (`notice_stat_id`),
  ADD KEY `fk_notice_status_establishment` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notice_status`
--
ALTER TABLE `notice_status`
  MODIFY `notice_stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_establishment` FOREIGN KEY (`id`) REFERENCES `establishments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notice_status`
--
ALTER TABLE `notice_status`
  ADD CONSTRAINT `fk_notice_status_establishment` FOREIGN KEY (`id`) REFERENCES `establishments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

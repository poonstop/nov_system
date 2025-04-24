-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 11:15 AM
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
  `remarks` text NOT NULL,
  `nov_files` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `issued_by` varchar(255) DEFAULT NULL,
  `issued_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`id`, `name`, `address`, `owner_representative`, `nature`, `products`, `violations`, `remarks`, `nov_files`, `created_at`, `issued_by`, `issued_datetime`) VALUES
(1, 'abc company', '123 main street la union ', NULL, 'Manufacturing', 'vapes', 'Invalid/Expired Accreditation', 'urgent pls', 'abccompany_1742967191.txt', '2025-03-26 05:33:11', NULL, NULL),
(4, 'topwood', 'agoo la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'pending', 'topwood_1742971309.txt', '2025-03-26 06:41:49', NULL, NULL),
(6, 'vape shop', 'aringay, la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark', 'pls confirm', 'vapeshop_1742972686.txt', '2025-03-26 07:04:46', NULL, NULL),
(7, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742972751.txt', '2025-03-26 07:05:51', NULL, NULL),
(8, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742973191.txt', '2025-03-26 07:13:11', NULL, NULL),
(9, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742973214.txt', '2025-03-26 07:13:34', NULL, NULL),
(10, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742973802.txt', '2025-03-26 07:23:22', NULL, NULL),
(11, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742973824.txt', '2025-03-26 07:23:44', NULL, NULL),
(12, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742974061.txt', '2025-03-26 07:27:41', NULL, NULL),
(13, 'vape shop', 'aringay, la union', NULL, 'Food Service', 'vape', 'No PS/ICC Mark', 'irgent', 'vapeshop_1742974096.txt', '2025-03-26 07:28:16', NULL, NULL),
(14, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'unreg', 'hjshop_1742974390.txt', '2025-03-26 07:33:10', NULL, NULL),
(15, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'unreg', 'hjshop_1742974651.txt', '2025-03-26 07:37:31', NULL, NULL),
(16, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'unreg', 'hjshop_1742974684.txt', '2025-03-26 07:38:04', NULL, NULL),
(17, 'hj shop', 'aringay, la union', NULL, 'Manufacturing', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'unreg', 'hjshop_1742975155.txt', '2025-03-26 07:45:55', NULL, NULL),
(18, 'Lebron shoe shop', 'san fernando city la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'investigate', 'Lebronshoeshop_1742976182.txt', '2025-03-26 08:03:02', NULL, NULL),
(19, 'hj shop', 'aringay, la union', NULL, 'Retail Trade', 'vape', 'No PS/ICC Mark, Invalid/Expired Accreditation', '', 'hjshop_1742978484.txt', '2025-03-26 08:41:24', NULL, NULL),
(20, 'The Bar', 'sta lucia, aringay, la union', NULL, 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation, Improper Labeling, Price Tag Violations', 'urgent', 'TheBar_1742978626.txt', '2025-03-26 08:43:46', NULL, NULL),
(21, 'the shoe lab', 'aringay la union', 'joshwil jason', 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'urgent', 'theshoelab_1742979638.txt', '2025-03-26 09:00:38', NULL, NULL),
(22, 'the beatles', 'san fernando city la union', 'john lennon', 'Manufacturing', 'guitar', 'No PS/ICC Mark, Price Tag Violations', 'arrest', 'thebeatles_1743034900.txt', '2025-03-27 00:21:40', NULL, NULL),
(23, 'lebron bron shop', 'los angeles pampanga', 'lebron jarmes', 'Retail Trade', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'URGENT!!!', 'lebronbronshop_1743038720.txt', '2025-03-27 01:25:20', NULL, NULL),
(24, 'usa shopping center', 'angeles pampanga', 'barack obams', 'Manufacturing', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'URGENT!!!!', 'usashoppingcenter_1743039054.txt', '2025-03-27 01:30:54', NULL, NULL),
(25, 'jake company', 'san fernando city la union', 'jake paul', 'youtube', 'vapes', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'confirm', 'jakecompany_1743042966.txt', '2025-03-27 02:36:06', NULL, NULL),
(26, 'vsvjnksnvjsn', 'vjknssjkdsv', 'ncjancksc', 'Manufacturing', 'vdsvsdvsdv', 'No PS/ICC Mark, Invalid/Expired Accreditation', 'svsdvsdv', 'vsvjnksnvjsn_1743043042.txt', '2025-03-27 02:37:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nov_records`
--

CREATE TABLE `nov_records` (
  `id` int(11) NOT NULL,
  `establishment_name` varchar(255) NOT NULL,
  `business_address` varchar(255) NOT NULL,
  `nature_of_business` varchar(100) NOT NULL,
  `non_conforming_products` text NOT NULL,
  `violations` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nov_records`
--

INSERT INTO `nov_records` (`id`, `establishment_name`, `business_address`, `nature_of_business`, `non_conforming_products`, `violations`, `remarks`, `created_at`) VALUES
(1, 'Sample Establishment', '123 Main Street', 'Retailer/Wholesaler', 'Non-conforming item description', 'No PS/ICC Mark, Improper Labeling', 'Follow-up required', '2025-03-25 02:19:31'),
(2, 'BANGBANG', 'SAN FERNANDO CITY', 'Hardware', 'VAPES', 'Invalid/Expired Accreditation', 'URGENT!!', '2025-03-25 03:06:23'),
(3, 'Mobiel legends', 'santa cruz, ilocos sur', 'Others', 'vapes', 'Invalid/Expired Accreditation', 'urgent', '2025-03-25 06:14:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`) VALUES
(1, 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', ''),
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc'),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2'),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3');

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
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nov_records`
--
ALTER TABLE `nov_records`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `nov_records`
--
ALTER TABLE `nov_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

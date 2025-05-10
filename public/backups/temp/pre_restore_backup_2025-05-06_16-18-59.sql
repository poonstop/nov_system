-- Database Backup for nov_system7
-- Generated: 2025-05-06 16:18:59

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
-- 5 rows
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `fullname`, `ulvl`, `status`, `email`) VALUES
(1, 'admin', '$2y$10$5LVnzakhGLnDG5KIkh7EReeWdPlx4iA9Bk7Hsh.OQOD6mQNjeiRg2', '', 'admin', 'active', ''),
(2, 'hjdc', '$2y$10$6L/KFzcBwpf0TRU0N2wsCOl1.JGBPZXOna2myG8poMx3eL0RBwN.y', 'hjdc', '', '', ''),
(3, 'admin2', '$2y$10$vdcpv3ELU8b6kkKqyRYTQO.fPpZpahFFcI8xM1uTui/BARbQ9/5WG', 'admin2', '', '', ''),
(4, 'admin3', '$2y$10$T/CSwDkjEYGng.NH9y9zMebdS025KQ.r6lbDydg.h7ThjBWCDu23q', 'admin3', '', '', ''),
(5, '958', '$2y$10$h5l3nz9V7RxgC3PATWrQbu9D0gWSN9uApLiCjPH5w7l1t.Z8cjOwm', 'Jack Bright', 'inspector', 'active', 'nanimo@nani');

-- Data for table `addresses`
-- 20 rows
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
(20, 20, 'fgdf', 'dgdgd', 'gdfdfg', 'fdgdfgdf', 'dfgddfg');


-- Set proper delimiter for routines
DELIMITER $$


-- Reset delimiter
DELIMITER ;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

-- End of backup

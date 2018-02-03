-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2017 at 10:34 PM
-- Server version: 10.1.25-MariaDB
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nlt77`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `coupon_name` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `c_description` text COLLATE latin1_general_cs,
  `item_type` text COLLATE latin1_general_cs NOT NULL,
  `discount` float NOT NULL DEFAULT '0.01',
  `count` int(11) NOT NULL DEFAULT '1',
  `store_name` varchar(32) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`coupon_name`, `c_description`, `item_type`, `discount`, `count`, `store_name`) VALUES
('Betty Crocker Cake', 'Get any Betty Crocker Cake', 'Betty Crocker Cake', 1, 4, 'Walmart'),
('Bounty Paper Towel Roll', 'Any one Paper Roll.', 'Bounty Paper Towel Roll', 0.5, 5, 'Kroger'),
('Dawn Dish Soap', 'Any one Bottle of Dawn Dish Soap.', 'Dawn Dish Soap', 0.25, 3, 'Kroger'),
('Progresso Soup', 'When you buy any four Progresso Soup Products.', 'Progresso Soup', 1, 8, 'Kroger'),
('Tide Detergent', 'Any one Tide Detergent', 'Tide Detergent', 0.75, 5, 'Walmart'),
('Windex', 'Save $.50 on any Windex Product.', 'Windex', 0.5, 4, 'Vowells'),
('Ziploc Bag', 'When you buy any two Ziploc Products.', 'Ziploc Bag', 1, 10, 'Vowells');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `store_name` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `s_description` text COLLATE latin1_general_cs,
  `username` varchar(15) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`store_name`, `s_description`, `username`) VALUES
('Kroger', 'Starkville', 'Alice'),
('Vowells', 'Starkville', 'Jimmy'),
('Walmart', 'Starkville', 'Bobby');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `password` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `password_reset` tinyint(1) NOT NULL DEFAULT '0',
  `user_type` varchar(11) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `password_reset`, `user_type`) VALUES
('Alice', 'alice@yahoo.com', '56f6a21de1642e264aeb7fa2ef9ea997', 0, 'store_owner'),
('Avan', 'avan@yahoo.com', 'c3643a5bd5f6e28a70504c772bfcb840', 0, 'admin'),
('Bobby', 'bobby@gmail.com', '7eddb313c6d3b5092f501a1a09faf813', 0, 'store_owner'),
('Cameron', 'cameron@outlook.com', '8dd7a5c6d571b3b368f4858770328bc5', 0, 'admin'),
('Christian', 'christian@gmail.com', '69dd9bd1c041ae2a9758db9f4e438cec', 0, 'admin'),
('Jimmy', 'jimmy@gmail.com', '920688bd88d6a1ba0e177f17bdf6dddd', 0, 'store_owner'),
('Nathan', 'nathan@microsoft.com', '4d91d8d3eb24d3c9a66be062b06b1305', 0, 'admin'),
('User1', 'user1@gmail.com', '3515ca5389aeb59eb0e541474e092bf6', 0, 'user'),
('User2', 'user2@gmail.com', 'e18d94e065b87660a2630cdc78e4d05c', 0, 'user'),
('User3', 'user3@gmail.com', '3517b9006fda6ada8cace26ac2ec0a14', 0, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `users_coupons`
--

CREATE TABLE `users_coupons` (
  `username` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `coupon_name` varchar(32) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `user_type` varchar(11) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`user_type`) VALUES
('admin'),
('store_owner'),
('user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`coupon_name`),
  ADD KEY `inventory` (`store_name`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`store_name`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users` (`user_type`);

--
-- Indexes for table `users_coupons`
--
ALTER TABLE `users_coupons`
  ADD PRIMARY KEY (`username`,`coupon_name`),
  ADD KEY `users_coupons` (`coupon_name`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`user_type`),
  ADD KEY `user_type` (`user_type`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`store_name`) REFERENCES `stores` (`store_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_type`) REFERENCES `user_types` (`user_type`);

--
-- Constraints for table `users_coupons`
--
ALTER TABLE `users_coupons`
  ADD CONSTRAINT `users_coupons_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_coupons_ibfk_2` FOREIGN KEY (`coupon_name`) REFERENCES `inventory` (`coupon_name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 05:52 PM
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
-- Database: `athwebs_database_ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `accumulable`
--

CREATE TABLE `accumulable` (
  `id` int(11) NOT NULL,
  `department_id_fk` int(11) NOT NULL,
  `item_id_fk` int(11) NOT NULL,
  `accumulable_quantity` int(11) NOT NULL,
  `consumed` int(11) NOT NULL,
  `school_year` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accumulable`
--

INSERT INTO `accumulable` (`id`, `department_id_fk`, `item_id_fk`, `accumulable_quantity`, `consumed`, `school_year`) VALUES
(84, 0, 22, 0, 0, '2023-2024'),
(85, 1, 22, 0, 0, '2023-2024'),
(86, 2, 22, 0, 0, '2023-2024'),
(87, 3, 22, 0, 0, '2023-2024'),
(88, 4, 22, 0, 27, '2023-2024'),
(89, 5, 22, 0, 0, '2023-2024'),
(90, 9, 22, 0, 0, '2023-2024'),
(91, 0, 23, 0, 0, '2023-2024'),
(92, 1, 23, 0, 44, '2023-2024'),
(93, 2, 23, 0, 0, '2023-2024'),
(94, 3, 23, 0, 0, '2023-2024'),
(95, 4, 23, 0, 13, '2023-2024'),
(96, 5, 23, 0, 0, '2023-2024'),
(97, 9, 23, 0, 0, '2023-2024'),
(98, 0, 24, 0, 0, '2023-2024'),
(99, 1, 24, 0, 0, '2023-2024'),
(100, 2, 24, 0, 0, '2023-2024'),
(101, 3, 24, 0, 0, '2023-2024'),
(102, 4, 24, 0, 7, '2023-2024'),
(103, 5, 24, 0, 0, '2023-2024'),
(104, 9, 24, 0, 0, '2023-2024');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`) VALUES
(0, 'General'),
(1, 'Math'),
(2, 'Advisers'),
(3, 'Admin'),
(4, 'Non-Teaching'),
(5, 'Test'),
(9, 'test2');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_category` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_brand` varchar(255) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `beginning_inventory` int(11) NOT NULL,
  `item_stocks` int(11) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `item_price` double(10,2) NOT NULL,
  `restock_indicator` int(11) NOT NULL,
  `borrowable` enum('yes','no') NOT NULL,
  `item_status` enum('available','not available') NOT NULL DEFAULT 'available',
  `date_added` datetime NOT NULL,
  `hide_status` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_category`, `item_name`, `item_brand`, `item_description`, `beginning_inventory`, `item_stocks`, `unit`, `item_price`, `restock_indicator`, `borrowable`, `item_status`, `date_added`, `hide_status`) VALUES
(22, 'Paper', 'Bondpaper', 'Pandayan', 'Letter', 100, 73, 'PCS', 1.00, 50, 'no', 'available', '2025-03-06 01:17:09', 'no'),
(23, 'Pen', 'Ballpen', 'Panda', 'Black', 50, 37, 'PCS', 10.00, 40, 'no', 'available', '2025-03-06 01:17:44', 'no'),
(24, 'Tools', 'Puncher', 'Brand', 'Large', 5, 3, 'PCS', 5.00, 0, 'yes', 'available', '2025-03-06 01:18:12', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `items_category`
--

CREATE TABLE `items_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items_category`
--

INSERT INTO `items_category` (`category_id`, `category_name`) VALUES
(6, 'Paper'),
(7, 'Pen'),
(8, 'Tools');

-- --------------------------------------------------------

--
-- Table structure for table `items_unit`
--

CREATE TABLE `items_unit` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items_unit`
--

INSERT INTO `items_unit` (`unit_id`, `unit_name`) VALUES
(3, 'PCS'),
(4, 'BOX'),
(5, 'REAM');

-- --------------------------------------------------------

--
-- Table structure for table `requested_items`
--

CREATE TABLE `requested_items` (
  `requested_items_id` int(11) NOT NULL,
  `item_id_fk` int(11) NOT NULL,
  `request_id_fk` int(11) NOT NULL,
  `request_quantity` int(11) NOT NULL,
  `requesting_department_id` int(11) NOT NULL,
  `return_status` enum('no','marked','returned','na') NOT NULL DEFAULT 'na',
  `returned_date` datetime DEFAULT NULL,
  `static_item_price` double(10,2) NOT NULL,
  `is_rejected` varchar(255) NOT NULL DEFAULT 'no',
  `stock_monitoring_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requested_items`
--

INSERT INTO `requested_items` (`requested_items_id`, `item_id_fk`, `request_id_fk`, `request_quantity`, `requesting_department_id`, `return_status`, `returned_date`, `static_item_price`, `is_rejected`, `stock_monitoring_id`) VALUES
(245, 22, 144, 25, 4, 'na', NULL, 0.00, 'no', 197),
(246, 23, 144, 11, 4, 'na', NULL, 0.00, 'no', 198),
(247, 24, 144, 5, 4, 'returned', '2025-03-06 01:26:00', 0.00, 'no', 199),
(248, 22, 145, 1, 1, 'na', NULL, 0.00, 'no', 0),
(249, 23, 146, 1, 0, 'na', NULL, 0.00, 'yes', 0),
(253, 23, 149, 39, 1, 'na', NULL, 0.00, 'no', 201),
(254, 23, 150, 5, 1, 'na', NULL, 0.00, 'no', 0),
(255, 22, 151, 1, 4, 'na', NULL, 0.00, 'no', 204),
(256, 23, 151, 1, 4, 'na', NULL, 0.00, 'no', 205),
(257, 24, 151, 1, 4, 'no', NULL, 0.00, 'no', 206),
(258, 24, 152, 1, 4, 'no', NULL, 0.00, 'no', 207),
(259, 22, 153, 1, 4, 'na', NULL, 0.00, 'no', 208),
(260, 23, 153, 1, 4, 'na', NULL, 0.00, 'no', 209);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `requestor_id` int(11) NOT NULL,
  `coordinator_id` int(11) DEFAULT NULL,
  `finance_id` int(11) NOT NULL,
  `charged_department` int(11) NOT NULL,
  `request_description` varchar(255) NOT NULL,
  `coordinator_comment` varchar(255) NOT NULL,
  `finance_comment` varchar(255) NOT NULL,
  `school_year` varchar(255) NOT NULL,
  `requested_date` datetime NOT NULL,
  `needed_date` date NOT NULL,
  `released_date` datetime DEFAULT NULL,
  `received_date` datetime NOT NULL,
  `request_status` enum('coordinator approval','finance approval','releasing','confirmation','completed','rejected') NOT NULL,
  `coordinator_approval` varchar(255) DEFAULT NULL,
  `finance_approval` varchar(255) DEFAULT NULL,
  `requestor_approval` varchar(255) DEFAULT NULL,
  `request_group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `requestor_id`, `coordinator_id`, `finance_id`, `charged_department`, `request_description`, `coordinator_comment`, `finance_comment`, `school_year`, `requested_date`, `needed_date`, `released_date`, `received_date`, `request_status`, `coordinator_approval`, `finance_approval`, `requestor_approval`, `request_group_id`) VALUES
(144, 9, 8, 8, 4, '8', '', 'asdfasdf', '2023-2024', '2025-03-06 01:18:52', '2025-03-08', '2025-03-06 01:24:39', '2025-03-06 01:26:22', 'completed', NULL, 'APPROVED', 'APPROVED', NULL),
(145, 8, 7, 8, 1, 'f', 'asdf', 'asdf', '2023-2024', '2025-03-06 01:31:33', '2025-03-10', '2025-03-06 23:21:42', '0000-00-00 00:00:00', 'confirmation', 'APPROVED', 'APPROVED', NULL, 1),
(146, 1, 7, 8, 1, '2', '', '', '2023-2024', '2025-03-06 01:35:36', '2025-03-15', NULL, '0000-00-00 00:00:00', 'rejected', NULL, NULL, NULL, 1),
(149, 9, 7, 8, 1, 'asdf', 'asdf', 'asdf', '2023-2024', '2025-03-07 00:08:14', '2025-03-09', '2025-03-07 00:48:24', '0000-00-00 00:00:00', 'releasing', 'APPROVED', 'APPROVED', NULL, 2),
(150, 7, 7, 8, 1, 'asdf', 'asdf', 'asdf', '2023-2024', '2025-03-07 00:09:02', '2025-03-09', '2025-03-07 00:48:24', '0000-00-00 00:00:00', 'releasing', 'APPROVED', 'APPROVED', NULL, 2),
(151, 8, 8, 8, 4, '', '', 'asdf', '2023-2024', '2025-03-09 20:20:10', '2025-03-12', '2025-03-09 20:25:53', '0000-00-00 00:00:00', 'confirmation', NULL, 'APPROVED', NULL, NULL),
(152, 9, 8, 8, 4, 'asdf', '', 'asdf', '2023-2024', '2025-03-09 20:26:33', '2025-03-12', '2025-03-09 20:27:18', '0000-00-00 00:00:00', 'confirmation', NULL, 'APPROVED', NULL, 3),
(153, 8, 8, 8, 4, 'asdf', '', 'asdf', '2023-2024', '2025-03-09 20:26:44', '2025-03-13', '2025-03-09 20:27:18', '0000-00-00 00:00:00', 'confirmation', NULL, 'APPROVED', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `school_year_id` int(11) NOT NULL,
  `school_year` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_year`
--

INSERT INTO `school_year` (`school_year_id`, `school_year`) VALUES
(1, '2023-2024'),
(2, '2022-2023');

-- --------------------------------------------------------

--
-- Table structure for table `stock_monitoring`
--

CREATE TABLE `stock_monitoring` (
  `monitoring_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `requesting_department_id` int(11) NOT NULL,
  `beginning_inventory_general` int(11) NOT NULL,
  `item_purchases` int(11) NOT NULL,
  `item_cost` double(10,2) NOT NULL,
  `requested_quantity_general` int(11) NOT NULL,
  `requested_date` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `ending_inventory_general` int(11) NOT NULL,
  `school_year` varchar(255) NOT NULL,
  `timestamp_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `charged_department_id` int(11) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `is_shown` varchar(255) NOT NULL DEFAULT 'yes',
  `is_borrowable` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_monitoring`
--

INSERT INTO `stock_monitoring` (`monitoring_id`, `request_id`, `item_id`, `requesting_department_id`, `beginning_inventory_general`, `item_purchases`, `item_cost`, `requested_quantity_general`, `requested_date`, `release_date`, `ending_inventory_general`, `school_year`, `timestamp_added`, `charged_department_id`, `purpose`, `is_shown`, `is_borrowable`) VALUES
(197, 144, 22, 4, 100, 0, 1.00, 25, '2025-03-06 01:18:52', '2025-03-06 01:24:39', 75, '2023-2024', '2025-03-05 17:24:39', 4, '', 'yes', 'no'),
(198, 144, 23, 4, 50, 0, 10.00, 11, '2025-03-06 01:18:52', '2025-03-06 01:24:39', 39, '2023-2024', '2025-03-05 17:24:39', 4, '', 'yes', 'no'),
(199, 144, 24, 4, 5, 0, 5.00, 5, '2025-03-06 01:18:52', '2025-03-06 01:24:39', 0, '2023-2024', '2025-03-05 17:24:39', 4, '', 'no', 'yes'),
(200, 144, 24, 4, 0, 0, 5.00, 5, '2025-03-06 01:18:52', '2025-03-06 01:26:00', 5, '2023-2024', '2025-03-05 17:26:00', 4, 'Returned', 'yes', 'yes'),
(201, 149, 23, 1, 39, 0, 10.00, 39, '2025-03-07 00:08:14', '2025-03-07 00:48:24', 0, '2023-2024', '2025-03-06 16:48:24', 1, '', 'yes', 'no'),
(202, 0, 23, 0, 0, 39, 0.00, 0, '2025-03-07 01:01:55', '2025-03-07 01:01:55', 39, '2023-2024', '2025-03-06 17:01:55', 0, 'Add Stocks', 'yes', ''),
(204, 151, 22, 4, 75, 0, 1.00, 1, '2025-03-09 20:20:10', '2025-03-09 20:25:53', 74, '2023-2024', '2025-03-09 12:25:53', 4, '', 'yes', 'no'),
(205, 151, 23, 4, 39, 0, 10.00, 1, '2025-03-09 20:20:10', '2025-03-09 20:25:53', 38, '2023-2024', '2025-03-09 12:25:53', 4, '', 'yes', 'no'),
(206, 151, 24, 4, 5, 0, 5.00, 1, '2025-03-09 20:20:10', '2025-03-09 20:25:53', 4, '2023-2024', '2025-03-09 12:25:53', 4, '', 'yes', 'yes'),
(207, 152, 24, 4, 4, 0, 5.00, 1, '2025-03-09 20:26:33', '2025-03-09 20:27:18', 3, '2023-2024', '2025-03-09 12:27:18', 4, '', 'yes', 'yes'),
(208, 153, 22, 4, 74, 0, 1.00, 1, '2025-03-09 20:26:44', '2025-03-09 20:27:18', 73, '2023-2024', '2025-03-09 12:27:18', 4, '', 'yes', 'no'),
(209, 153, 23, 4, 38, 0, 10.00, 1, '2025-03-09 20:26:44', '2025-03-09 20:27:18', 37, '2023-2024', '2025-03-09 12:27:18', 4, '', 'yes', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `access_level` enum('employee','admin','coordinator','finance officer','inventory manager') NOT NULL,
  `department` varchar(255) NOT NULL,
  `handled_department` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_status` enum('pending','active','disabled') NOT NULL,
  `creation_date` datetime NOT NULL,
  `otp` varchar(10) NOT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `access_level`, `department`, `handled_department`, `username`, `email`, `contact`, `password`, `account_status`, `creation_date`, `otp`, `otp_expiry`) VALUES
(1, 'Ian Angelo', 'Meneses', 'Simbulan', 'employee', '1, 4', 0, 'asdf', 'salinas.nathalie1902@gmail.com', '', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-14 17:33:20', '487883', '2024-09-02 12:39:35'),
(6, 'admin', 'admin', 'admin', 'admin', '3', 0, 'admin', 'admin@gmail.com', '09271340561', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-24 01:10:22', '', NULL),
(7, 'math', 'math', 'coordinator', 'coordinator', '1, 2', 1, 'math', 'math@gmail.com', '09166672259', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-26 15:27:46', '034360', '2024-09-02 13:13:49'),
(8, 'finance', 'finance', 'Officer', 'finance officer', '4, 1', 4, 'finance', 'smbnangelo16@gmail.com', '12345678910', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-26 15:28:16', '', NULL),
(9, 'inventory', '', 'manager', 'inventory manager', '4, 3, 1', 0, 'inventory', '', '09166672259', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-26 15:29:12', '', NULL),
(10, 'science', '', 'coordinator', 'coordinator', '2', 2, 'science', 'science@gmail.com', '09166672259', '912ec803b2ce49e4a541068d495ab570', 'active', '2024-04-26 16:14:28', '', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accumulable`
--
ALTER TABLE `accumulable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id_fk` (`department_id_fk`),
  ADD KEY `item_id_fk` (`item_id_fk`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `items_category`
--
ALTER TABLE `items_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `items_unit`
--
ALTER TABLE `items_unit`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `requested_items`
--
ALTER TABLE `requested_items`
  ADD PRIMARY KEY (`requested_items_id`),
  ADD KEY `requested_items_ibfk_1` (`request_id_fk`),
  ADD KEY `requested_items_ibfk_2` (`item_id_fk`),
  ADD KEY `requesting_department_id` (`requesting_department_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `requests_ibfk_1` (`requestor_id`),
  ADD KEY `charged_department` (`charged_department`);

--
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`school_year_id`);

--
-- Indexes for table `stock_monitoring`
--
ALTER TABLE `stock_monitoring`
  ADD PRIMARY KEY (`monitoring_id`),
  ADD KEY `stock_monitoring_ibfk_1` (`requesting_department_id`),
  ADD KEY `stock_monitoring_ibfk_2` (`item_id`),
  ADD KEY `stock_monitoring_ibfk_3` (`request_id`),
  ADD KEY `charged_department_id` (`charged_department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accumulable`
--
ALTER TABLE `accumulable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `items_category`
--
ALTER TABLE `items_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `items_unit`
--
ALTER TABLE `items_unit`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requested_items`
--
ALTER TABLE `requested_items`
  MODIFY `requested_items_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_monitoring`
--
ALTER TABLE `stock_monitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accumulable`
--
ALTER TABLE `accumulable`
  ADD CONSTRAINT `accumulable_ibfk_1` FOREIGN KEY (`department_id_fk`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `accumulable_ibfk_2` FOREIGN KEY (`item_id_fk`) REFERENCES `items` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `requested_items`
--
ALTER TABLE `requested_items`
  ADD CONSTRAINT `requested_items_ibfk_1` FOREIGN KEY (`request_id_fk`) REFERENCES `requests` (`request_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `requested_items_ibfk_2` FOREIGN KEY (`item_id_fk`) REFERENCES `items` (`item_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `requested_items_ibfk_3` FOREIGN KEY (`requesting_department_id`) REFERENCES `departments` (`department_id`) ON UPDATE NO ACTION;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`requestor_id`) REFERENCES `users` (`user_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`charged_department`) REFERENCES `departments` (`department_id`) ON UPDATE NO ACTION;

--
-- Constraints for table `stock_monitoring`
--
ALTER TABLE `stock_monitoring`
  ADD CONSTRAINT `stock_monitoring_ibfk_1` FOREIGN KEY (`requesting_department_id`) REFERENCES `departments` (`department_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `stock_monitoring_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

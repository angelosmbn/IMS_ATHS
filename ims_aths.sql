-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 05:36 PM
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
-- Database: `ims_aths`
--

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
(1, 'MAPEH'),
(2, 'Mathematics'),
(3, 'Science'),
(4, 'Finance'),
(5, 'Admin'),
(8, 'Example Department');

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
  `item_stocks` int(11) NOT NULL,
  `item_price` double(10,2) NOT NULL,
  `restock_quantity` int(11) DEFAULT 0,
  `item_status` enum('Available','Not Available') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_category`, `item_name`, `item_brand`, `item_description`, `item_stocks`, `item_price`, `restock_quantity`, `item_status`) VALUES
(42, 'Ballpen', 'Ballpen', 'Pilot', 'Black', 9, 15.00, 10, 'Available'),
(43, 'Bond Paper', 'Bond Paper', 'Hard Copy', 'Short', 198, 1.50, 50, 'Available'),
(44, 'Calculator', 'Calculator', 'Casio', 'Scientific Calculator', 8, 1099.00, 0, 'Available'),
(45, 'Crayons', 'Crayons', 'Crayola', '16 Crayon', 72, 30.00, 0, 'Available'),
(46, 'Glue', 'Glue', 'Elmer\\\'s', 'White', 12, 18.00, 0, 'Available'),
(47, 'Keyboard', 'Keyboard', 'A4tech', 'Membrane', 35, 150.00, 0, 'Available'),
(48, 'Monitor', 'Monitor', 'Asus', '24inch 240hz', 36, 25000.00, 0, 'Available'),
(49, 'Mouse', 'Mouse', 'Logitech', '1000 Polling Rate', 37, 1800.00, 0, 'Available'),
(50, 'Pencil', 'Pencil', 'Mongol', '1', 50, 10.00, 10, 'Available'),
(51, 'Ruler', 'Ruler', 'Joy', '12 inch, Transparent', 12, 10.00, 0, 'Available'),
(52, 'Scissors', 'Scissors', 'Joy', 'Large', 15, 25.00, 0, 'Available'),
(53, 'Bond Paper', 'Bond Paper', 'Hard Copy', 'Legal', 147, 1.75, 50, 'Available'),
(54, 'Scissors', 'asdf', 'asdf', 'asdf', 20, 1.00, 11, 'Available');

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
(32, 'Bond Paper'),
(33, 'Ballpen'),
(34, 'Pencil'),
(35, 'Glue'),
(36, 'Crayons'),
(37, 'Scissors'),
(38, 'Ruler'),
(39, 'Calculator'),
(40, 'Monitor'),
(41, 'Mouse'),
(42, 'Keyboard'),
(43, 'test');

--
-- Triggers `items_category`
--
DELIMITER $$
CREATE TRIGGER `update_item_category` AFTER UPDATE ON `items_category` FOR EACH ROW BEGIN
    UPDATE items
    SET item_category = NEW.category_name
    WHERE item_category = OLD.category_name;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `needed_date` date NOT NULL,
  `received_date` date DEFAULT NULL,
  `requested_items` varchar(255) NOT NULL,
  `items_quantity` varchar(255) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `coordinator_signature` varchar(255) DEFAULT NULL,
  `finance_signature` varchar(255) DEFAULT NULL,
  `requestor_signature` varchar(255) DEFAULT NULL,
  `received_signature` varchar(255) DEFAULT NULL,
  `school_year` varchar(255) NOT NULL,
  `request_status` enum('Coordinator Approval','Finance Approval','Releasing','Confirmation','Completed','Rejected') NOT NULL DEFAULT 'Coordinator Approval',
  `description` varchar(255) NOT NULL,
  `coordinator_name` varchar(255) NOT NULL,
  `finance_name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `request_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `user_id`, `user_name`, `request_date`, `needed_date`, `received_date`, `requested_items`, `items_quantity`, `department_name`, `coordinator_signature`, `finance_signature`, `requestor_signature`, `received_signature`, `school_year`, `request_status`, `description`, `coordinator_name`, `finance_name`, `comment`, `request_timestamp`) VALUES
(160, 21, 'Angelo M. Simbulan', '2024-04-04', '2024-04-07', '2024-04-04', '43,53', '50,50', 'Mathematics', '1712217089_sig 2.png', '1712217128_sig 3.png', '1712217031_sig 1.png', '1712217198_sig 1.png', '2023-2024', 'Completed', 'Test', 'Cinderella C. Math', 'Tremaine O. Finance', '', '2024-04-04 07:53:18'),
(161, 17, 'Paul F. Walker', '2024-04-04', '2024-04-07', '2024-04-04', '42,43,53,44,45,46,47,48,49', '1,2,3,1,2,1,5,4,3', 'Mathematics', NULL, '1712245604_signature with background.jpg', '1712245568_signature with background.jpg', '1712245635_signature with background.jpg', '2023-2024', 'Completed', '', '', 'Tremaine O. Finance', '', '2024-04-04 15:47:15');

-- --------------------------------------------------------

--
-- Table structure for table `stock_monitoring`
--

CREATE TABLE `stock_monitoring` (
  `monitoring_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `beginning_inventory` int(11) NOT NULL,
  `item_purchases` int(11) NOT NULL,
  `item_cost` double(10,2) NOT NULL,
  `request_quantity` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `release_date` date NOT NULL,
  `ending_inventory` int(11) NOT NULL,
  `requesting_department` varchar(255) NOT NULL,
  `school_year` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_monitoring`
--

INSERT INTO `stock_monitoring` (`monitoring_id`, `item_id`, `request_id`, `beginning_inventory`, `item_purchases`, `item_cost`, `request_quantity`, `request_date`, `release_date`, `ending_inventory`, `requesting_department`, `school_year`) VALUES
(74, 43, 160, 150, 0, 1.50, 50, '2024-04-04', '2024-04-04', 100, 'Mathematics', '2023-2024'),
(75, 53, 160, 200, 0, 1.75, 50, '2024-04-04', '2024-04-04', 150, 'Mathematics', '2023-2024'),
(76, 54, 0, 10, 10, 0.00, 0, '2024-04-04', '0000-00-00', 20, 'Added Stocks', ''),
(77, 43, 0, 100, 100, 0.00, 0, '2024-04-04', '0000-00-00', 200, 'Added Stocks', ''),
(78, 42, 0, 0, 10, 0.00, 0, '2024-04-04', '0000-00-00', 10, 'Added Stocks', ''),
(79, 42, 161, 10, 0, 15.00, 1, '2024-04-04', '2024-04-04', 9, 'Mathematics', '2023-2024'),
(80, 43, 161, 200, 0, 1.50, 2, '2024-04-04', '2024-04-04', 198, 'Mathematics', '2023-2024'),
(81, 53, 161, 150, 0, 1.75, 3, '2024-04-04', '2024-04-04', 147, 'Mathematics', '2023-2024'),
(82, 44, 161, 9, 0, 1099.00, 1, '2024-04-04', '2024-04-04', 8, 'Mathematics', '2023-2024'),
(83, 45, 161, 74, 0, 30.00, 2, '2024-04-04', '2024-04-04', 72, 'Mathematics', '2023-2024'),
(84, 46, 161, 13, 0, 18.00, 1, '2024-04-04', '2024-04-04', 12, 'Mathematics', '2023-2024'),
(85, 47, 161, 40, 0, 150.00, 5, '2024-04-04', '2024-04-04', 35, 'Mathematics', '2023-2024'),
(86, 48, 161, 40, 0, 25000.00, 4, '2024-04-04', '2024-04-04', 36, 'Mathematics', '2023-2024'),
(87, 49, 161, 40, 0, 1800.00, 3, '2024-04-04', '2024-04-04', 37, 'Mathematics', '2023-2024');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `username` varchar(255) NOT NULL,
  `contact` varchar(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access_level` enum('user','admin','coordinator','finance officer','inventory manager') NOT NULL,
  `handled_department` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `account_status` enum('active','disabled','pending') NOT NULL DEFAULT 'pending',
  `created_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `middle_name`, `gender`, `username`, `contact`, `password`, `access_level`, `handled_department`, `department`, `account_status`, `created_on`) VALUES
(8, 'George', 'Admin', '', 'Female', 'admin@gmail.com', '09123456789', '912ec803b2ce49e4a541068d495ab570', 'admin', '', 'Admin', 'active', '2024-04-03 07:24:19'),
(17, 'Paul', 'Walker', 'Fast', 'Male', 'inventorymanager@gmail.com', '09123456789', '912ec803b2ce49e4a541068d495ab570', 'inventory manager', '', 'Finance', 'active', '2024-04-03 07:36:31'),
(18, 'Cinderella', 'Math', 'Coordinator', 'Female', 'mathcoordinator@gmail.com', '09123456789', '912ec803b2ce49e4a541068d495ab570', 'coordinator', 'Mathematics', 'Mathematics', 'active', '2024-04-03 07:36:15'),
(19, 'Tremaine', 'Finance', 'Officer', 'Female', 'financeofficer@gmail.com', '09123456789', '912ec803b2ce49e4a541068d495ab570', 'finance officer', '', 'Finance', 'active', '2024-04-03 07:32:50'),
(20, 'asdf', 'asdf', 'asdf', 'Male', 'asdf@gmail.com', 'asdf', '912ec803b2ce49e4a541068d495ab570', 'coordinator', 'MAPEH', 'MAPEH', 'active', '2024-04-03 14:19:18'),
(21, 'Angelo', 'Simbulan', 'Meneses', 'Male', '202110310@fit.edu.ph', '09271340561', '912ec803b2ce49e4a541068d495ab570', 'user', '', 'Mathematics', 'active', '2024-04-04 07:49:35'),
(22, 'Ian Angelo', 'Simbulan', 'Meneses', 'Male', 'angelosimbulan16@gmail.com', '09271340561', '912ec803b2ce49e4a541068d495ab570', 'coordinator', 'Admin', 'Admin', 'active', '2024-04-04 16:32:40'),
(23, 'asdf', 'asdf', 'asdf', 'Male', 'smbnangelo16@gmail.com', 'asdf', '912ec803b2ce49e4a541068d495ab570', 'user', '', '', 'pending', '2024-04-04 16:18:10');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stock_monitoring`
--
ALTER TABLE `stock_monitoring`
  ADD PRIMARY KEY (`monitoring_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `items_category`
--
ALTER TABLE `items_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `stock_monitoring`
--
ALTER TABLE `stock_monitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

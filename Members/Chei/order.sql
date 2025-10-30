-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 08:47 AM
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
-- Database: `bhps`
--

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` varchar(200) NOT NULL,
  `BookID` varchar(200) NOT NULL,
  `order_date` date NOT NULL,
  `address` varchar(200) NOT NULL,
  `Quantity` int(200) NOT NULL,
  `User_ID` varchar(200) NOT NULL,
  `Status` varchar(200) NOT NULL,
  `Total_Amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderItemID`, `OrderID`, `BookID`, `order_date`, `address`, `Quantity`, `User_ID`, `Status`, `Total_Amount`, `payment_method`, `card_number`, `bank_name`) VALUES
(1, '843006', 'B029', '2025-10-15', '1234dfghfb', 1, 'user002', 'Delivery', 35.00, 'bank', '', 'Maybank2u'),
(2, '952641', 'B029', '2025-10-15', '1234dfghfb', 1, 'user002', 'Processing', 35.00, 'bank', '', 'Agrobank'),
(3, '909334', 'B008', '2025-10-15', '1234dfghfb', 1, 'user002', 'Processing', 25.00, 'bank', '', 'Agrobank'),
(4, '775278', 'E002', '2025-10-15', '1234dfghfb', 1, 'user001', 'Processing', 20.00, 'card', '2345 6789 8765 4323', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD UNIQUE KEY `unique_order_book` (`OrderID`,`BookID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

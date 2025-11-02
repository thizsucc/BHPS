-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 07:54 PM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` varchar(200) NOT NULL,
  `AdminName` varchar(200) NOT NULL,
  `Jawatan` text NOT NULL,
  `Password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `AdminName`, `Jawatan`, `Password`) VALUES
('admin001', 'Ken', 'Admin', '$2y$10$BK3dXhLK4Ng.99GnM27Afe/mnpHb3sISyKJ5jTMNVYDdCmp23jhnq');

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `BookID` varchar(200) NOT NULL,
  `Name` text NOT NULL,
  `Author` varchar(255) DEFAULT NULL,
  `Category` varchar(200) NOT NULL,
  `Format` varchar(200) NOT NULL,
  `Price` varchar(200) NOT NULL,
  `Quantity` int(200) NOT NULL,
  `ImagePath` varchar(200) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`BookID`, `Name`, `Author`, `Category`, `Format`, `Price`, `Quantity`, `ImagePath`, `Description`) VALUES
('B001', 'I have no mouth & I must Scream', 'Harlan Ellison', 'Fiction', 'Physical', '45.9', 20, 'uploads/1759130743_IHaveNoMouth.jpg', 'A'),
('B002', 'DUNE', 'Frank Herbert', 'Fiction', 'Physical', '48.5', 20, 'uploads/1759166159_Dune.jpg', 'Discover this amazing book that will take you on an unforgettable journey through its pages. Perfect for readers of all ages.'),
('B003', 'Spider-Man Forever Young', 'Stefan Petrucha', 'Fiction', 'Physical', '52.9', 20, 'uploads/1759209951_spdrman.jpg', ''),
('B004', 'Venom', 'Dony Cate', 'Fiction', 'Physical', '55.2', 20, 'uploads/1759209987_Venom.jpg', ''),
('B005', 'The Little Prince', 'Frank Herber', 'Fiction', 'Physical', '47.9', 20, 'uploads/1759210025_theprince.jpg', ''),
('B006', 'Ready Player One', 'Ernest Cline', 'Fiction', 'Physical', '53.9', 20, 'uploads/1759214846_rpo.jpg', ''),
('B007', 'Ready Player Two', 'Ernest Cline', 'Fiction', 'Physical', '53.9', 20, 'uploads/1759214880_rpt.jpg', ''),
('B008', ' Chainsaw man Chapter 20 Volume 2', 'Tatsuki Fujimoto', 'Fiction', 'Physical', '48.5', 20, 'uploads/1759718078_csm.png', ''),
('B009', ' Xenophone', 'Hellenika', 'Acedemic', 'Physical', '45.9', 5, 'uploads/1759432637_Xenophon.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.'),
('B010', 'Ikigai', 'Héctor García', 'Academic', 'Physical', '48.5', 1, 'uploads/1759432714_ikigai.png', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.'),
('B011', 'The First 90 Days', 'Michael D. Watkins', 'Business', 'Physical', '48.5', 2, 'uploads/1759435802_the90D.avif', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.'),
('B012', ' Illicit Obsession', 'J.A Owenby', 'Romance', 'Physical', '45.9', 20, 'uploads/1759435860_illicit.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.'),
('B013', ' The diary of Wimpy Kid', 'Jeff Kinney', 'Children', 'Physical', '45.9', 20, 'uploads/1759436348_wimpyKid.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.'),
('B014', ' Delicious Air Fryer', 'mob', 'Food & Drink', 'Physical', '45.9', 20, 'uploads/1759436166_airFryer.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin'),
('B015', 'Nocturne', 'Lydia Madison', 'Romance', 'Physical', '49.9', 10, 'uploads/1759508023_nocturne.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` varchar(200) NOT NULL,
  `BookID` varchar(200) NOT NULL,
  `order_date` date NOT NULL,
  `address` varchar(200) NOT NULL,
  `Quantity` int(200) NOT NULL,
  `User_ID` varchar(200) NOT NULL,
  `Status` varchar(200) NOT NULL,
  `Total_Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` varchar(200) NOT NULL,
  `Staff_Name` varchar(200) NOT NULL,
  `Jawatan` text NOT NULL,
  `Password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `Staff_Name`, `Jawatan`, `Password`) VALUES
('staff001', 'Shawn', 'Manager', '$2y$10$8D..u0SQ/GLW17IfaPH4eum.rzr1/NkCSsphG38JhNSSotGg68f7G');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` varchar(200) NOT NULL,
  `user_Name` varchar(200) NOT NULL,
  `EmailAddress` varchar(200) NOT NULL,
  `address` varchar(200) NOT NULL,
  `FirstName` varchar(200) NOT NULL,
  `LastName` varchar(200) NOT NULL,
  `Password` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `user_Name`, `EmailAddress`, `address`, `FirstName`, `LastName`, `Password`) VALUES
('user001', 'cheikahyan', '123@gmail.com', '', 'chei', 'kah yan', '$2y$10$5/brlLEPtkPFcP5amfq3/O7knY4eg5QOTpHxa1O5TVfjRH1m.WPCC'),
('user002', 'huda', 'huda@gmail.com', '', 'huda', 'huda', '$2y$10$PJdyXQlJOTigvuWspLect.xzXFGH843XsBEAHafM3e8hEZDJmIizq'),
('user003', 'hilmi', 'hilmi@gmail.com', '', 'hilmi', 'hilmi', '$2y$10$mUC1QWbtEmqseWJXAwWBaecB6Dow8fbst5mLCS3.IuYn6cSVTdvkm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`BookID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

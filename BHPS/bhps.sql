-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 04:32 AM
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
  `Description` text DEFAULT NULL,
  `is_promotion` tinyint(1) DEFAULT 0,
  `promotion_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`BookID`, `Name`, `Author`, `Category`, `Format`, `Price`, `Quantity`, `ImagePath`, `Description`, `is_promotion`, `promotion_price`) VALUES
('B001', 'I have no mouth & I must Scream', 'Harlan Ellison', 'Fiction', 'Physical', '45.9', 20, 'uploads/1759130743_IHaveNoMouth.jpg', 'A', 0, NULL),
('B002', 'DUNE', 'Frank Herbert', 'Fiction', 'Physical', '48.5', 20, 'uploads/1759166159_Dune.jpg', 'Discover this amazing book that will take you on an unforgettable journey through its pages. Perfect for readers of all ages.', 0, NULL),
('B003', 'Spider-Man Forever Young', 'Stefan Petrucha', 'Fiction', 'Physical', '52.9', 20, 'uploads/1759209951_spdrman.jpg', '', 0, NULL),
('B004', 'Venom', 'Dony Cate', 'Fiction', 'Physical', '55.2', 20, 'uploads/1759209987_Venom.jpg', '', 0, NULL),
('B005', 'The Little Prince', 'Frank Herber', 'Fiction', 'Physical', '47.9', 20, 'uploads/1759210025_theprince.jpg', 'This talk about the little prince.', 0, NULL),
('B006', 'Ready Player One', 'Ernest Cline', 'Fiction', 'Physical', '53.9', 20, 'uploads/1759214846_rpo.jpg', '', 0, NULL),
('B007', 'Ready Player Two', 'Ernest Cline', 'Fiction', 'Physical', '53.9', 20, 'uploads/1759214880_rpt.jpg', '', 0, NULL),
('B008', ' Chainsaw man Chapter 20 Volume 2', 'Tatsuki Fujimoto', 'Fiction', 'Physical', '48.5', 20, 'uploads/1759718078_csm.png', '', 1, 25.00),
('B009', ' Xenophone', 'Hellenika', 'Acedemic', 'Physical', '45.9', 5, 'uploads/1759432637_Xenophon.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B010', 'Ikigai', 'Héctor García', 'Academic', 'Physical', '48.5', 1, 'uploads/1759432714_ikigai.png', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B011', 'The First 90 Days', 'Michael D. Watkins', 'Business', 'Physical', '48.5', 2, 'uploads/1759435802_the90D.avif', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B012', ' Illicit Obsession', 'J.A Owenby', 'Romance', 'Physical', '45.9', 20, 'uploads/1759435860_illicit.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B013', ' The diary of Wimpy Kid', 'Jeff Kinney', 'Children', 'Physical', '45.9', 20, 'uploads/1759436348_wimpyKid.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B014', ' Delicious Air Fryer', 'mob', 'Food & Drink', 'Physical', '45.9', 20, 'uploads/1759436166_airFryer.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 0, NULL),
('B015', 'Nocturne', 'Lydia Madison', 'Romance', 'Physical', '49.9', 10, 'uploads/1759508023_nocturne.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B016', 'The Age of Wood', 'Roland Ennos', 'Academic', 'Physical', '49.9', 20, 'uploads/1760023434_ageofwood.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B017', 'Atomic Habits', 'Stefan Petrucha', 'Academic', 'Physical', '52.9', 10, 'uploads/1760023507_atomicHabits.png', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B018', 'Proposal Book', 'Laura Portwood-Stacer', 'Academic', 'Physical', '55.2', 30, 'uploads/1760023573_proposal.png', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B019', 'The Wimpy Kid Movie Diary: The Next Chapter', 'Jeff Kinney', 'Children', 'Physical', '46.9', 10, 'uploads/1760023630_movieDiary.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B020', ' Fantastic Beasts', 'J.K. Rowling', 'Children', 'Physical', '49.6', 20, 'uploads/1760023798_fantasticBeast.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B021', 'Captain Underpants', 'Dav Pilkey', 'Children', 'Physical', '42.9', 8, 'uploads/1760024043_captain.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B022', 'Hilda and the Black Hound', 'Luke Pearson', 'Children', 'Physical', '47.9', 19, 'uploads/1760024101_hilda.avif', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B023', 'Business to Business Marketing', 'Ross Brennan', 'Business', 'Physical', '45.9', 30, 'uploads/1760024153_btom.jpg', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B024', 'Strong Ground', 'Brene Brown', 'Business', 'Physical', '49.9', 17, 'uploads/1760024248_strongG.jpg', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B025', ' Deep Work', 'Cal Newport', 'Business', 'Physical', '52.9', 20, 'uploads/1760024384_deepwork.jpg', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B026', 'Automatic Noodle', 'Annalee Newitz', 'Food & Drink', 'Physical', '48.5', 15, 'uploads/1760027511_automaticNoodle.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 0, NULL),
('B027', 'Chilli and Cheese', 'Kunzang Choden', 'Food & Drink', 'Physical', '49.9', 9, 'uploads/1760027583_chilli.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 0, NULL),
('B028', 'CRAVE', 'Ed Smith', 'Food & Drink', 'Physical', '52.9', 10, 'uploads/1760027661_crave.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 0, NULL),
('B029', 'Drunken Botanist', 'Amy Stewart', 'Food & Drink', 'Physical', '55.2', 20, 'uploads/1760027708_drunkenBotanist.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 1, 35.00),
('B030', 'Food & Drink Inforgraphics', 'Taschen', 'Food & Drink', 'Physical', '47.9', 20, 'uploads/1760028515_foodNdrink.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 0, NULL),
('B031', ' Lights Out', 'Navessa Allen', 'Romance', 'Physical', '48.5', 20, 'uploads/1760028562_lightOut.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B032', 'Witch of Wild things', 'Raquel Vasquez Gilliland', 'Romance', 'Physical', '52.9', 20, 'uploads/1760028608_witchThing.avif', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.\r\n', 1, 40.00),
('B033', 'A Witch Guide to Magical Innkeeping', 'Sangu Mandanna', 'Romance', 'Physical', '55.2', 19, 'uploads/1760028655_witchGuide.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B034', 'Five Feet Apart', 'Rachael Lippincott', 'Romance', 'Physical', '53.9', 20, 'uploads/1760028718_fiveFeet.avif', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B035', 'the GIRL and the GHOST', 'Hanna Alkaf', 'Romance', 'Physical', '56.5', 20, 'uploads/1760028754_girlAndGhost.avif', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 0, NULL),
('B036', 'A History Ancient Rome In Twelve Coins', 'Gareth Harney', 'Academic', 'Physical', '47.9', 20, 'uploads/1760028847_rome.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 1, 25.00),
('B037', 'Apple in China', 'Patrick Mcgee', 'Academic', 'Physical', '53.9', 25, 'uploads/1760028889_appleChina.png', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B038', 'Light Eater', 'Zoe Chlanger', 'Academic', 'Physical', '56.5', 34, 'uploads/1760028931_lightEater.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('B039', 'Kikis Delivery Service', 'Eiko Kadono', 'Children', 'Physical', '50', 20, 'uploads/1760029008_kikisDelivery.avif', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B040', 'When I Was a Kid', 'Cheeming Boey', 'Children', 'Physical', '39.9', 30, 'uploads/1760029054_whenIkid.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('B041', 'Business to Business Marketing', 'Ross Brennan', 'Business', 'Physical', '55.2', 23, 'uploads/1760029158_btom2.jpg', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B042', 'The Let Them', 'Mel Robbins', 'Business', 'Physical', '47.9', 24, 'uploads/1760029203_images.jpg', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('B043', ' Surrounded by idiots', 'Thomas Edison', 'Business', 'Physical', '53.9', 20, 'uploads/1760029255_surround.webp', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('E001', 'I have no mouth & I must Scream (E-book)', 'Harlan Ellison', 'Fiction', 'E-book', '20', 100, 'uploads/1760258153_IHaveNoMouth.jpg', 'A', 0, NULL),
('E002', 'Xenophone (E-book)', 'Hellenika', 'Academic', 'E-book', '20', 100, 'uploads/1760258311_Xenophon.jpg', 'An insightful academic book that offers a comprehensive exploration of knowledge, fostering critical thinking and deeper understanding of its subject matter.', 0, NULL),
('E003', 'The First 90 Days (E-book)', 'Michael D. Watkins', 'Business', 'E-book', '20', 100, 'uploads/1760258702_the90D.avif', 'An insightful business book that will take you on a transformative journey through strategy, innovation, and real-world success.', 0, NULL),
('E004', ' Illicit Obsession (E-book)', 'J.A Owenby', 'Romance', 'E-book', '20', 100, 'uploads/1760258773_illicit.jpg', 'A heartwarming romance book that will take you on an unforgettable journey of love, passion, and emotion.', 1, 18.00),
('E005', 'The diary of Wimpy Kid (E-book)', 'Jeff Kinney', 'Children', 'E-book', '20', 100, 'uploads/1760258826_wimpyKid.webp', 'A delightful children’s book that will take young readers on a fun-filled journey of imagination, learning, and adventure.', 0, NULL),
('E006', 'Delicious Air Fryer (E-book)', 'mob', 'Food & Drink', 'E-book', '20', 100, 'uploads/1760258875_airFryer.jpg', 'A flavorful food and drink book that will take you on a delicious journey through recipes, culture, and culin', 1, 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'hilmi', 'hilmi@gmail.com', 'okay', 'okay', '2025-10-30 15:14:26');

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
(1, '843006', 'B029', '2025-10-15', '21,Jalan No123, Taman melaka, 434653, Melaka', 1, 'user002', 'Completed', 35.00, 'bank', '', 'Maybank2u'),
(2, '952641', 'B029', '2025-10-15', 'No 1, Jalan Bunga Pudding, Apartment Ali, 77000, Melaka', 1, 'user002', 'Completed', 35.00, 'bank', '', 'Agrobank'),
(3, '909334', 'B008', '2025-10-15', 'No 12, Jalan Bunga Pudding, Apartment Ali, 77000, Melaka', 1, 'user002', 'Delivery', 25.00, 'bank', '', 'Agrobank'),
(4, '775278', 'E002', '2025-10-15', 'Block A, Jalan tun Ali, Apartment Ali, 77000, Melaka', 1, 'user001', 'Processing', 20.00, 'card', '2345 6789 8765 4323', ''),
(5, '408349', 'B015', '2025-10-15', '435, Jalan Idamai, Taman Berjaya, 56100, KL', 1, 'user003', 'Delivery', 49.90, 'card', '4567 8765 4323 4448', ''),
(6, '408349', 'B030', '2025-10-15', 'No 11, Jalan Berjaya, Apartment Berjaya, 56100, Cheras', 1, 'user003', 'Delivery', 47.90, 'card', '4567 8765 4323 4448', ''),
(7, '807890', 'B027', '2025-10-15', 'No 10, Jalan Bunga Pudding, Apartment Ali, 77000, Melaka', 1, 'user003', 'Cancelled', 49.90, 'bank', '', 'CIMB Clicks'),
(8, '874173', 'B036', '2025-10-15', '36, Jalan NO425, Taman Laut, 56100, Kuala Lumpur', 1, 'user001', 'Processing', 25.00, 'bank', '', 'Maybank2u'),
(9, '874173', 'E004', '2025-10-15', '201, Jalan Indah, Taman Indah, 56100, Kuala Lumpur', 1, 'user001', 'Processing', 20.00, 'bank', '', 'Maybank2u'),
(10, '431245', 'B010', '2025-10-23', '36, Jalan NO425, Taman Laut, 56100, Kuala Lumpur', 1, 'user001', 'Completed', 48.50, 'bank', '', 'Agrobank'),
(11, '431245', 'B042', '2025-10-23', '56, Jalan Meriah, Taman Sahabat, 81200, Johor Baru, Johor', 1, 'user001', 'Completed', 47.90, 'bank', '', 'Agrobank'),
(12, '431245', 'B018', '2025-10-23', '21, Jalan NO582, Apartment Seri Sahabat, 56100, Kuala Lumpur', 1, 'user001', 'Completed', 55.20, 'bank', '', 'Agrobank'),
(13, '276207', 'B015', '2025-10-27', '55, Jalan Baik Hati, Taman Nilai, 83000, Johor', 1, 'user001', 'Processing', 49.90, 'bank', '', 'CIMB Clicks'),
(14, '250107', 'B042', '2025-10-27', '21,Jalan Panadol, Taman Panadol, 68000, KL', 1, 'user001', 'Processing', 47.90, 'card', '3456 7898 7643 7256', ''),
(15, '272798', 'B033', '2025-10-27', '83, Jalan meriah, Taman Subang, 56100, Kuala Lumpur', 1, 'user001', 'Processing', 55.20, 'bank', '', 'Maybank2u'),
(16, '601408', 'B008', '2025-10-29', 'No12, Block B, Jalan Hang Tuah, 72100, Melaka', 2, 'user001', 'Processing', 50.00, 'card', '1234 5698 7654 3214', ''),
(17, '601408', 'B025', '2025-10-29', 'No18, Block B, Jalan Hang Tuah, 72100, Melaka', 1, 'user001', 'Processing', 52.90, 'card', '1234 5698 7654 3214', ''),
(18, '202745', 'B026', '2025-10-29', 'No29, Block B, Jalan Hang Tuah, 72100, Melaka', 1, 'user002', 'Processing', 48.50, 'bank', '', 'Agrobank'),
(19, '128841', 'E004', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 18.00, 'card', '2467 9976 5339 9925', ''),
(20, '128841', 'B036', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 25.00, 'card', '2467 9976 5339 9925', ''),
(21, '128841', 'B004', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 55.20, 'card', '2467 9976 5339 9925', ''),
(22, '128841', 'B002', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 48.50, 'card', '2467 9976 5339 9925', ''),
(23, '128841', 'B003', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 52.90, 'card', '2467 9976 5339 9925', ''),
(24, '128841', 'E001', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 20.00, 'card', '2467 9976 5339 9925', ''),
(25, '201622', 'E004', '2025-10-29', '20,Jalan 216, Taman Raya, KL', 1, 'user003', 'Processing', 18.00, 'bank', '', 'Maybank2u'),
(26, '538354', 'B030', '2025-10-29', '20,Jalan 216, Taman Raya, KL', 1, 'user003', 'Processing', 47.90, 'card', '5432 8935 4993 3222', ''),
(27, '555352', 'B010', '2025-10-29', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 48.50, 'bank', '', 'Maybank2u'),
(28, '666882', 'B015', '2025-10-30', 'No25,Jalan Bunga Bangsar, Taman Melaka Raya, 77125, Melaka Raya, Melaka', 1, 'user003', 'Processing', 49.90, 'bank', '', 'Maybank2u');

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
('user001', 'cheikahyan', 'cheikahyan@gmail.com', '', 'chei', 'kah yan', '$2y$10$5/brlLEPtkPFcP5amfq3/O7knY4eg5QOTpHxa1O5TVfjRH1m.WPCC'),
('user002', 'huda', 'huda@gmail.com', '', 'huda', 'huda', '$2y$10$PJdyXQlJOTigvuWspLect.xzXFGH843XsBEAHafM3e8hEZDJmIizq'),
('user003', 'hilmi', 'hilmi@gmail.com', '', 'hilmi', 'hilmi', '$2y$10$mUC1QWbtEmqseWJXAwWBaecB6Dow8fbst5mLCS3.IuYn6cSVTdvkm'),
('user004', 'Rifqah', 'rifqah@gmail.com', '', 'Rifqah', 'Rifqah', '$2y$10$opzuh6JfRph81DYbuKLjzObEPOXeNrYI9KUjyz/h8MOPAEwo4oZ7.'),
('user005', 'tohkhimtat', 'tohkhimtat@gmail.com', '', 'Toh', 'Khim Tat', '$2y$10$m2bVsQv0l/DAErTHCZ03kuU/sICDYbQsAWj/ALgjcP4D7bH7wTZDO');

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
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD UNIQUE KEY `unique_order_book` (`OrderID`,`BookID`);

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

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

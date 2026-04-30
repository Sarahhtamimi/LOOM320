-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 30, 2026 at 03:11 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loom`
--

-- --------------------------------------------------------

--
-- Table structure for table `Admin`
--

CREATE TABLE `Admin` (
  `admin_id` int NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Admin`
--

INSERT INTO `Admin` (`admin_id`, `email`, `password`) VALUES
(5, 'jana.admin@gmail.com', '$2y$10$QpVdpuVaEezHTdDP/o/TD.JXat5Hs8sVYPe0yq4J8jL2Yibtgdr1.');

-- --------------------------------------------------------

--
-- Table structure for table `BlogPost`
--

CREATE TABLE `BlogPost` (
  `blog_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publish_date` date NOT NULL,
  `admin_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Bookmark`
--

CREATE TABLE `Bookmark` (
  `bookmark_id` int NOT NULL,
  `user_id` int NOT NULL,
  `secondUseItem_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Bookmark`
--

INSERT INTO `Bookmark` (`bookmark_id`, `user_id`, `secondUseItem_id`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 2, 1),
(4, 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `SecondUseItem`
--

CREATE TABLE `SecondUseItem` (
  `secondUseItem_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contact_method` varchar(150) NOT NULL
) ;

--
-- Dumping data for table `SecondUseItem`
--

INSERT INTO `SecondUseItem` (`secondUseItem_id`, `user_id`, `title`, `photo`, `description`, `price`, `contact_method`) VALUES
(1, 1, 'Grey Wool Cardigan', 'images/grey-cardigan.jpg', 'A soft grey wool cardigan suitable for everyday outfits. It is pre-loved and still in good condition.', 85.00, 'jana@example.com'),
(2, 2, 'Striped Bow-Back Dress', 'images/striped-bow-dress.jpg', 'A striped dress with a bow-back detail. A simple second-use piece for a soft feminine look.', 110.00, 'sara@example.com'),
(3, 3, 'Draped Green Top', 'images/draped-green-top.jpg', 'A green draped top that can be styled casually or semi-formally. It supports reusing clothing instead of buying new.', 70.00, 'noor@example.com'),
(4, 1, 'Ivory Rose Top', 'images/ivory-rose-top.jpg', 'An ivory top with delicate rose details. A pre-loved item for users who prefer soft and elegant pieces.', 65.00, 'jana@example.com'),
(5, 2, 'Polka Dot Midi Skirt', 'images/polka-dot-skirt.jpg', 'A polka dot midi skirt in good condition. It can be matched with simple tops for a classic outfit.', 90.00, 'sara@example.com'),
(6, 3, 'Wide-Leg Linen Trousers', 'images/linen-trousers.jpg', 'Comfortable wide-leg linen trousers with a relaxed fit. A useful second-use piece for a more conscious wardrobe.', 100.00, 'noor@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `user_id` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'Jana', 'jana@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(2, 'Sara', 'sara@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(3, 'Noor', 'noor@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(4, 'ahmad', 'ahmad@gmail.com', '$2y$10$i8tZQWPxkV4FiyrijmPtJ.NJumDs1oOa/Zu2VnTzS/c9ZmRXpkU.W');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Admin`
--
ALTER TABLE `Admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `BlogPost`
--
ALTER TABLE `BlogPost`
  ADD PRIMARY KEY (`blog_id`),
  ADD KEY `fk_blog_admin` (`admin_id`);

--
-- Indexes for table `Bookmark`
--
ALTER TABLE `Bookmark`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `uq_user_saved_item` (`user_id`,`secondUseItem_id`),
  ADD KEY `fk_bookmark_user` (`user_id`),
  ADD KEY `fk_bookmark_seconduse` (`secondUseItem_id`);

--
-- Indexes for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  ADD PRIMARY KEY (`secondUseItem_id`),
  ADD KEY `fk_seconduse_user` (`user_id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_user_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Admin`
--
ALTER TABLE `Admin`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `BlogPost`
--
ALTER TABLE `BlogPost`
  MODIFY `blog_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Bookmark`
--
ALTER TABLE `Bookmark`
  MODIFY `bookmark_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  MODIFY `secondUseItem_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `BlogPost`
--
ALTER TABLE `BlogPost`
  ADD CONSTRAINT `fk_blog_admin` FOREIGN KEY (`admin_id`) REFERENCES `Admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Bookmark`
--
ALTER TABLE `Bookmark`
  ADD CONSTRAINT `fk_bookmark_seconduse` FOREIGN KEY (`secondUseItem_id`) REFERENCES `SecondUseItem` (`secondUseItem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  ADD CONSTRAINT `fk_seconduse_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

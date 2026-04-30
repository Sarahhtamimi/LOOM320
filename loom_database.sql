-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 30, 2026 at 03:24 PM
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
(1, 1, 'Grey Wool Cardigan', 'images/grey cardigan.jpg.jpg', 'A soft grey wool cardigan suitable for everyday outfits. It is pre-loved and still in good condition.', 85.00, 'jana@example.com'),
(2, 2, 'Striped Bow-Back Dress', 'images/purpleDress.jpg', 'A striped dress with a bow-back detail. A simple second-use piece for a soft feminine look.', 110.00, 'sara@example.com'),
(3, 3, 'Draped Green Top', 'images/Draped Green Top.jpg', 'A green draped top that can be styled casually or semi-formally. It supports reusing clothing instead of buying new.', 70.00, 'noor@example.com'),
(4, 1, 'Ivory Rose Top', 'images/yellow_shirt.jpg', 'An ivory top with delicate rose details. A pre-loved item for users who prefer soft and elegant pieces.', 65.00, 'jana@example.com'),
(5, 2, 'Polka Dot Midi Skirt', 'images/dottedSkirt.jpg', 'A polka dot midi skirt in good condition. It can be matched with simple tops for a classic outfit.', 90.00, 'sara@example.com'),
(6, 3, 'Wide-Leg Linen Trousers', 'images/blackPants.jpg', 'Comfortable wide-leg linen trousers with a relaxed fit. A useful second-use piece for a more conscious wardrobe.', 100.00, 'noor@example.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  ADD PRIMARY KEY (`secondUseItem_id`),
  ADD KEY `fk_seconduse_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  MODIFY `secondUseItem_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `SecondUseItem`
--
ALTER TABLE `SecondUseItem`
  ADD CONSTRAINT `fk_seconduse_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

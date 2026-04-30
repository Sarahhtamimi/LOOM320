-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: 30 أبريل 2026 الساعة 15:50
-- إصدار الخادم: 5.7.24
-- PHP Version: 8.3.1

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
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@loom.com', '$2y$12$mVZglvtahc4./c6ctnFRleL1a2cioRr/xV0azLMGr1wzFwKYH23VK'),
(2, 'jana.admin@gmail.com', '$2y$10$QpVdpuVaEezHTdDP/o/TD.JXat5Hs8sVYPe0yq4J8jL2Yibtgdr1.');

-- --------------------------------------------------------

--
-- بنية الجدول `blogpost`
--

CREATE TABLE `blogpost` (
  `blog_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publish_date` date NOT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `blogpost`
--

INSERT INTO `blogpost` (`blog_id`, `title`, `image`, `content`, `publish_date`, `admin_id`) VALUES
(1, 'Why Sustainable Fashion Matters', 'images/blog1.png', 'Sustainable fashion matters because it encourages people to think about how clothing is made, used, and reused. It supports better choices and helps reduce unnecessary waste.', '2026-04-01', 1),
(2, 'How Second-Use Clothing Extends a Garment’s Life', 'images/blog2.png', 'Second-use clothing gives garments another chance instead of letting them go to waste. Buying, selling, or donating pre-loved clothes helps extend their life cycle.', '2026-04-05', 1),
(3, '5 Smart Habits for a More Conscious Wardrobe', 'images/blog3.png', 'A more conscious wardrobe can start with simple habits such as buying less, choosing quality pieces, caring for clothes properly, repairing items, and supporting second-use fashion.', '2026-04-10', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `bookmark`
--

CREATE TABLE `bookmark` (
  `bookmark_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secondUseItem_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `bookmark`
--

INSERT INTO `bookmark` (`bookmark_id`, `user_id`, `secondUseItem_id`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 2, 1),
(4, 3, 4);

-- --------------------------------------------------------

--
-- بنية الجدول `seconduseitem`
--

CREATE TABLE `seconduseitem` (
  `secondUseItem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contact_method` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `seconduseitem`
--

INSERT INTO `seconduseitem` (`secondUseItem_id`, `user_id`, `title`, `photo`, `description`, `price`, `contact_method`) VALUES
(1, 1, 'Grey Wool Cardigan', 'images/grey cardigan.jpg.jpg', 'A soft grey wool cardigan suitable for everyday outfits. It is pre-loved and still in good condition.', '85.00', 'jana@example.com'),
(2, 2, 'Striped Bow-Back Dress', 'images/imagesstriped-bow-dress.jpg', 'A striped dress with a bow-back detail. A simple second-use piece for a soft feminine look.', '110.00', 'sara@example.com'),
(3, 3, 'Draped Green Top', 'images/Draped Green Top.jpg', 'A green draped top that can be styled casually or semi-formally. It supports reusing clothing instead of buying new.', '70.00', 'noor@example.com'),
(4, 1, 'Ivory Rose Top', 'images/yellow_shirt.jpg', 'An ivory top with delicate rose details. A pre-loved item for users who prefer soft and elegant pieces.', '65.00', 'jana@example.com'),
(5, 2, 'Polka Dot Midi Skirt', 'images/dottedSkirt.jpg', 'A polka dot midi skirt in good condition. It can be matched with simple tops for a classic outfit.', '90.00', 'sara@example.com'),
(6, 3, 'Wide-Leg Linen Trousers', 'images/blackPants.jpg', 'Comfortable wide-leg linen trousers with a relaxed fit. A useful second-use piece for a more conscious wardrobe.', '100.00', 'noor@example.com');

-- --------------------------------------------------------

--
-- بنية الجدول `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'Jana', 'jana@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(2, 'Sara', 'sara@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(3, 'Noor', 'noor@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(4, 'fufu', 'm@gmail.com', '$2y$10$sd94MRZrinjZRCrpS6lJ6uBbVGFKY2F2sRaSxkYlQKzmRcSXoHBUG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Indexes for table `blogpost`
--
ALTER TABLE `blogpost`
  ADD PRIMARY KEY (`blog_id`),
  ADD KEY `fk_blog_admin` (`admin_id`);

--
-- Indexes for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `uq_user_saved_item` (`user_id`,`secondUseItem_id`),
  ADD KEY `fk_bookmark_user` (`user_id`),
  ADD KEY `fk_bookmark_seconduse` (`secondUseItem_id`);

--
-- Indexes for table `seconduseitem`
--
ALTER TABLE `seconduseitem`
  ADD PRIMARY KEY (`secondUseItem_id`),
  ADD KEY `fk_seconduse_user` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_user_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blogpost`
--
ALTER TABLE `blogpost`
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seconduseitem`
--
ALTER TABLE `seconduseitem`
  MODIFY `secondUseItem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `blogpost`
--
ALTER TABLE `blogpost`
  ADD CONSTRAINT `fk_blog_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `fk_bookmark_seconduse` FOREIGN KEY (`secondUseItem_id`) REFERENCES `seconduseitem` (`secondUseItem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `seconduseitem`
--
ALTER TABLE `seconduseitem`
  ADD CONSTRAINT `fk_seconduse_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

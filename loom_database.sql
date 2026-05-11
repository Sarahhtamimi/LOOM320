-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 11, 2026 at 02:25 PM
-- Server version: 5.7.24
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@loom.com', '$2y$12$mVZglvtahc4./c6ctnFRleL1a2cioRr/xV0azLMGr1wzFwKYH23VK'),
(2, 'jana.admin@gmail.com', '$2y$10$QpVdpuVaEezHTdDP/o/TD.JXat5Hs8sVYPe0yq4J8jL2Yibtgdr1.');

-- --------------------------------------------------------

--
-- Table structure for table `blogpost`
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
-- Dumping data for table `blogpost`
--

INSERT INTO `blogpost` (`blog_id`, `title`, `image`, `content`, `publish_date`, `admin_id`) VALUES
(1, 'One Day Without Plastic', 'images/BLOG1.jpg', 'What if, just for one day… plastic disappeared from your wardrobe? Not just bottles or bags — but your clothes too.  \r\n\r\nTake a moment and look at what you are wearing right now. There is a high chance it contains synthetic fibers like polyester — materials deeply connected to plastic.  \r\n\r\nFast fashion made this invisible. Easy. Normal. Acceptable.  \r\n\r\nBut remove it… and everything changes.  \r\n\r\nYour outfit is no longer just comfort — it becomes a decision. You start questioning your choices. What is this made of? Why do I own it? Do I really need more?  \r\n\r\nWithout plastic, fashion slows down. It becomes intentional instead of automatic.  \r\n\r\nAnd in that moment, something shifts.  \r\n\r\nYou are no longer just wearing clothes.  \r\n\r\nYou are understanding them.', '2026-05-01', 1),
(2, 'The Timeline of Fast Fashion', 'images/BLOG2.jpg', 'We buy clothes for moments. A trend. A season. A single event.  \r\n\r\nThen we move on.  \r\n\r\nBut the clothes do not.  \r\n\r\nFast fashion feels temporary, but its impact is built to last. A shirt worn a few times can remain in landfills for decades — especially when made from synthetic fabrics.  \r\n\r\nThese materials do not disappear. They slowly break down, releasing microplastics into the environment. Invisible… but never gone.  \r\n\r\nEvery piece you throw away starts a timeline. Not yours — but the planet’s.  \r\n\r\nAnd that timeline stretches far beyond your memory of buying it.  \r\n\r\nThe real question is not how often you wear something.  \r\n\r\nIt is how long it will stay after you stop using it.', '2026-05-02', 1),
(3, 'If Everyone Bought Less', 'images/BLOG3.jpg', 'Skipping one purchase feels small.  \r\n\r\nWearing the same outfit again feels insignificant.  \r\n\r\nIt feels like it does not matter.  \r\n\r\nBut imagine something different.  \r\n\r\nWhat if everyone made that same decision?  \r\n\r\nMillions of people choosing to buy less. Millions of clothes not produced. Millions of resources not consumed.  \r\n\r\nSuddenly, the impact is no longer small.  \r\n\r\nThe fashion industry runs on demand — and demand is something we control.  \r\n\r\nReal change does not start with extreme actions.  \r\n\r\nIt starts with repeated small ones.  \r\n\r\nBecause when simple decisions spread…  \r\n\r\nthey do not stay small.  \r\n\r\nThey become a movement.', '2026-05-03', 1),
(4, 'The Truth About Sustainable Fashion', 'images/BLOG4.jpg', 'Sustainable fashion sounds simple. Buy better. Recycle more. Donate what you do not use.  \r\n\r\nBut reality is more complicated.  \r\n\r\nBuying “eco” brands does not solve overconsumption. Donating clothes does not erase waste.  \r\n\r\nThe problem is not only what we buy — it is how much we buy.  \r\n\r\nFast fashion exists because we accept constant change. New styles. New trends. New reasons to consume.  \r\n\r\nReal sustainability is different.  \r\n\r\nIt is slower. More intentional. More conscious.  \r\n\r\nIt asks you to pause before you buy.  \r\n\r\nTo question before you follow trends.  \r\n\r\nBecause the goal is not to consume better.  \r\n\r\nIt is to consume less.', '2026-05-04', 1),
(5, 'The Second Life of Your Clothes', 'images/BLOG5.jpg', 'What if your clothes never truly ended?  \r\n\r\nWe are used to thinking in cycles of use and disposal. You wear something. You get bored. You replace it.  \r\n\r\nBut fashion does not have to end there.  \r\n\r\nA piece of clothing can be restyled, repaired, or transformed into something new.  \r\n\r\nSecond-hand fashion carries stories. Upcycled pieces carry creativity.  \r\n\r\nSuddenly, your wardrobe is no longer static.  \r\n\r\nIt becomes something flexible. Something evolving.  \r\n\r\nThe moment you see clothes differently, they stop being disposable.  \r\n\r\nThey become valuable again.', '2026-05-05', 1),
(6, 'Fast Choices, Slow Consequences', 'images/BLOG6.jpg', 'Fast fashion moves quickly.  \r\n\r\nNew arrivals every week. New trends every moment. Constant change.  \r\n\r\nIt feels exciting. Endless. Effortless.  \r\n\r\nBut the consequences are not fast.  \r\n\r\nThey build slowly — through pollution, waste, and resource consumption.  \r\n\r\nEvery low-cost purchase carries a hidden impact that is not shown on the price tag.  \r\n\r\nIt exists somewhere else.  \r\n\r\nIn landfills. In water systems. In future limitations.  \r\n\r\nWhat feels like a simple decision today becomes a long-term reality tomorrow.  \r\n\r\nFast choices never stay fast.  \r\n\r\nTheir consequences always last longer.', '2026-05-06', 1),
(7, 'Your Fashion Footprint', 'images/BLOG7.jpg', 'Every outfit tells a story.  \r\n\r\nNot just about your style — but about your impact.  \r\n\r\nWhat you wear, how often you wear it, and why you bought it all matter.  \r\n\r\nNow imagine your day being measured differently.  \r\n\r\nNot by achievements… but by impact.  \r\n\r\nWearing something again becomes progress. Unnecessary purchases become visible.  \r\n\r\nSuddenly, your choices are clear.  \r\n\r\nAnd once you see them…  \r\n\r\nyou cannot ignore them.  \r\n\r\nFashion stops being just expression.  \r\n\r\nIt becomes responsibility.', '2026-05-07', 1),
(8, 'Before It Disappears', 'images/BLOG8.jpg', 'Some changes happen quietly.  \r\n\r\nToo quietly to notice at first.  \r\n\r\nA forest slightly smaller. Water slightly less clear. Air slightly heavier.  \r\n\r\nFashion plays a role in this — through materials, production, and constant demand.  \r\n\r\nBehind every piece of clothing, there is a chain of environmental impact.  \r\n\r\nAnd over time… it adds up.  \r\n\r\nThe danger is not just the damage.  \r\n\r\nIt is getting used to it.  \r\n\r\nBecause what becomes normal… becomes invisible.  \r\n\r\nAnd by the time we notice —  \r\n\r\nit may already be gone.', '2026-05-08', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `bookmark_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secondUseItem_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bookmark`
--

INSERT INTO `bookmark` (`bookmark_id`, `user_id`, `secondUseItem_id`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 2, 1),
(4, 3, 4),
(5, 6, 4),
(6, 6, 5),
(8, 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `seconduseitem`
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
-- Dumping data for table `seconduseitem`
--

INSERT INTO `seconduseitem` (`secondUseItem_id`, `user_id`, `title`, `photo`, `description`, `price`, `contact_method`) VALUES
(1, 1, 'Grey Wool Cardigan', 'images/grey cardigan.jpg.jpg', 'A soft grey wool cardigan suitable for everyday outfits. It is pre-loved and still in good condition.', '85.00', 'jana@example.com'),
(2, 2, 'Striped Bow-Back Dress', 'images/imagesstriped-bow-dress.jpg', 'A striped dress with a bow-back detail. A simple second-use piece for a soft feminine look.', '110.00', 'sara@example.com'),
(3, 3, 'Draped Green Top', 'images/Draped Green Top.jpg', 'A green draped top that can be styled casually or semi-formally. It supports reusing clothing instead of buying new.', '70.00', 'noor@example.com'),
(4, 1, 'Ivory Rose Top', 'images/yellow_shirt.jpg', 'An ivory top with delicate rose details. A pre-loved item for users who prefer soft and elegant pieces.', '65.00', 'jana@example.com'),
(5, 2, 'Polka Dot Midi Skirt', 'images/dottedSkirt.jpg', 'A polka dot midi skirt in good condition. It can be matched with simple tops for a classic outfit.', '90.00', 'sara@example.com'),
(6, 3, 'Wide-Leg Linen Trousers', 'images/blackPants.jpg', 'Comfortable wide-leg linen trousers with a relaxed fit. A useful second-use piece for a more conscious wardrobe.', '100.00', 'noor@example.com'),
(7, 6, 'jvfkv', 'images/uploads/item_20260506_204431_2b29b6ce.png', 'kuguk', '777.00', '0500621188');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'Jana', 'jana@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(2, 'Sara', 'sara@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(3, 'Noor', 'noor@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(4, 'fufu', 'm@gmail.com', '$2y$10$sd94MRZrinjZRCrpS6lJ6uBbVGFKY2F2sRaSxkYlQKzmRcSXoHBUG'),
(5, 'Sarah', 'jana@gmail.com', '$2y$10$811PZtGYgYdDGJT06x6dz.Ie3tSZs1BG3ZBTn4AFYKpS3IFMFtBTG'),
(6, 'Sarah', 'sarah@gmail.com', '$2y$10$VzGkJL7fXtSYlTfu9T/TxeKm/gY9evRXnAI1tsfM0qDX6IXAuRLcS');

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
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `seconduseitem`
--
ALTER TABLE `seconduseitem`
  MODIFY `secondUseItem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blogpost`
--
ALTER TABLE `blogpost`
  ADD CONSTRAINT `fk_blog_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `fk_bookmark_seconduse` FOREIGN KEY (`secondUseItem_id`) REFERENCES `seconduseitem` (`secondUseItem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `seconduseitem`
--
ALTER TABLE `seconduseitem`
  ADD CONSTRAINT `fk_seconduse_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

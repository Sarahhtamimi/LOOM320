-- LOOM Database
-- Database name: loom
-- Based on the LOOM relational schema and current website data.
-- Sample user password: user123
-- Sample admin password: admin123
-- Passwords are bcrypt hashes for PHP password_verify().

DROP DATABASE IF EXISTS `loom`;
CREATE DATABASE `loom` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `loom`;

CREATE TABLE `User` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Admin` (
  `admin_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `SecondUseItem` (
  `secondUseItem_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `photo` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `contact_method` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`secondUseItem_id`),
  KEY `fk_seconduse_user` (`user_id`),
  CONSTRAINT `fk_seconduse_user`
    FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `chk_seconduse_price`
    CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Bookmark` (
  `bookmark_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `secondUseItem_id` INT(11) NOT NULL,
  PRIMARY KEY (`bookmark_id`),
  KEY `fk_bookmark_user` (`user_id`),
  KEY `fk_bookmark_seconduse` (`secondUseItem_id`),
  UNIQUE KEY `uq_user_saved_item` (`user_id`, `secondUseItem_id`),
  CONSTRAINT `fk_bookmark_user`
    FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_bookmark_seconduse`
    FOREIGN KEY (`secondUseItem_id`) REFERENCES `SecondUseItem` (`secondUseItem_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `BlogPost` (
  `blog_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `publish_date` DATE NOT NULL,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`blog_id`),
  KEY `fk_blog_admin` (`admin_id`),
  CONSTRAINT `fk_blog_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `Admin` (`admin_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `User` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'Jana', 'jana@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(2, 'Sara', 'sara@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O'),
(3, 'Noor', 'noor@example.com', '$2y$12$Ztqf9Ott6DdYQZlB0iOiguKM7guve6LTQjGuaDttC41JhpJ2rp24O');

INSERT INTO `Admin` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@loom.com', '$2y$12$mVZglvtahc4./c6ctnFRleL1a2cioRr/xV0azLMGr1wzFwKYH23VK');

INSERT INTO `SecondUseItem` (`secondUseItem_id`, `user_id`, `title`, `photo`, `description`, `price`, `contact_method`) VALUES
(1, 1, 'Grey Wool Cardigan', 'images/grey-cardigan.jpg', 'A soft grey wool cardigan suitable for everyday outfits. It is pre-loved and still in good condition.', 85.00, 'jana@example.com'),
(2, 2, 'Striped Bow-Back Dress', 'images/striped-bow-dress.jpg', 'A striped dress with a bow-back detail. A simple second-use piece for a soft feminine look.', 110.00, 'sara@example.com'),
(3, 3, 'Draped Green Top', 'images/draped-green-top.jpg', 'A green draped top that can be styled casually or semi-formally. It supports reusing clothing instead of buying new.', 70.00, 'noor@example.com'),
(4, 1, 'Ivory Rose Top', 'images/ivory-rose-top.jpg', 'An ivory top with delicate rose details. A pre-loved item for users who prefer soft and elegant pieces.', 65.00, 'jana@example.com'),
(5, 2, 'Polka Dot Midi Skirt', 'images/polka-dot-skirt.jpg', 'A polka dot midi skirt in good condition. It can be matched with simple tops for a classic outfit.', 90.00, 'sara@example.com'),
(6, 3, 'Wide-Leg Linen Trousers', 'images/linen-trousers.jpg', 'Comfortable wide-leg linen trousers with a relaxed fit. A useful second-use piece for a more conscious wardrobe.', 100.00, 'noor@example.com');

INSERT INTO `Bookmark` (`bookmark_id`, `user_id`, `secondUseItem_id`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 2, 1),
(4, 3, 4);

INSERT INTO `BlogPost` (`blog_id`, `title`, `image`, `content`, `publish_date`, `admin_id`) VALUES
(1, 'Why Sustainable Fashion Matters', 'images/blog1.png', 'Sustainable fashion matters because it encourages people to think about how clothing is made, used, and reused. It supports better choices and helps reduce unnecessary waste.', '2026-04-01', 1),
(2, 'How Second-Use Clothing Extends a Garment’s Life', 'images/blog2.png', 'Second-use clothing gives garments another chance instead of letting them go to waste. Buying, selling, or donating pre-loved clothes helps extend their life cycle.', '2026-04-05', 1),
(3, '5 Smart Habits for a More Conscious Wardrobe', 'images/blog3.png', 'A more conscious wardrobe can start with simple habits such as buying less, choosing quality pieces, caring for clothes properly, repairing items, and supporting second-use fashion.', '2026-04-10', 1);

-- QuickCart Electronics E-commerce Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `offers`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT 'cat_default.jpg',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`name`, `image`) VALUES
('Headphones', 'cat_headphones.jpg'),
('PC Mouse', 'cat_mouse.jpg'),
('Keyboards', 'cat_keyboard.jpg'),
('Speakers', 'cat_speakers.jpg'),
('Accessories', 'cat_accessories.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 4.5,
  `image` varchar(255) DEFAULT 'prod_default.jpg',
  `stock` int(11) DEFAULT 50,
  `is_featured` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`cat_id`, `name`, `price`, `description`, `rating`, `image`, `is_featured`) VALUES
(1, 'SonicBoom X1 Wireless', 4999.00, 'Premium noise-cancelling wireless headphones with 40h battery life.', 4.8, 'headphones_1.png', 1),
(1, 'Aura Pods Pro', 2499.00, 'True wireless earbuds with deep bass and crystal clear calls.', 4.5, 'headphones_2.png', 0),
(2, 'SwiftGlide RGB Mouse', 1599.00, 'Ultra-fast gaming mouse with 16,000 DPI and customizable RGB.', 4.7, 'mouse_1.png', 1),
(2, 'SilentClick Office Mouse', 899.00, 'Ergonomic wireless mouse with silent clicks for productive work.', 4.3, 'mouse_2.png', 0),
(3, 'MechKeys V2 Mechanical', 6499.00, 'Full-sized mechanical keyboard with tactile blue switches.', 4.9, 'keyboard_1.png', 1),
(3, 'SlimType Wireless Keyboard', 2999.00, 'Ultra-slim aluminum keyboard with multi-device connectivity.', 4.6, 'keyboard_2.png', 0),
(4, 'BassThump Portable Speaker', 3499.00, 'IPX7 waterproof bluetooth speaker with 360-degree sound.', 4.7, 'speakers_1.png', 1),
(5, 'VoltCharge 65W GaN', 1999.00, 'Compact Triple-port fast charger for all your devices.', 4.8, 'acc_1.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `discount_percent` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `offers` (`title`, `promo_code`, `banner_image`, `discount_percent`) VALUES
('Launch Offer: 20% OFF', 'VOLT20', 'banner_launch.jpg', 20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default Admin (Password: admin123)
-- Hash: $2y$10$3LjEotT2CcZgEvmqtkODW.fokt1lB2mGcwS8z1jlU.M2RXj3cFMgy
INSERT INTO `users` (`username`, `email`, `phone`, `address`, `password`, `is_admin`) VALUES
('System Admin', 'admin@quickcart.com', '1234567890', 'QuickCart Headquarters', '$2y$10$3LjEotT2CcZgEvmqtkODW.fokt1lB2mGcwS8z1jlU.M2RXj3cFMgy', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT 'COD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 16, 2026 at 09:24 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rojam_boutique`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Rojam Admin', 'admin@rojam.test', '$2y$10$eAc88QoaxqF0VlAoZn1i9OI7kT0r6ICdh1qqo/lb0RvmJS0UEiB6u', '2026-08-17 12:15:27');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
(1, 'Dresses', '2026-08-17 11:18:10'),
(2, 'Jackets', '2026-08-17 11:18:10'),
(3, 'Tops', '2026-08-17 11:18:10'),
(4, 'Bottoms', '2026-08-17 11:18:10'),
(5, 'Shoes', '2026-08-17 11:18:10'),
(6, 'Scarf', '2026-08-17 12:18:46'),
(7, 'Decor', '2026-08-17 12:18:56'),
(8, 'Kitchen linen', '2026-08-17 12:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `phone`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Aleki', 'Wesa', '0701035848', 'alex@gmail.com', '$2y$10$V8PkgUItWlgLCy/JfIhAB.M7bULlQho.jpwI3EVwfEuIHTeYi1p0O', '2026-08-17 12:25:41'),
(2, 'Abby', 'Mwanduka', '0701033222', 'abby@gmail.com', '$2y$10$Q1iHltMj4z/QLc66rQ0KmeWlCukE8KPw/bZpFmKfYGgeUMtEGpa8K', '2026-08-17 21:33:59'),
(3, 'Joseph', 'Kamau', '0723456789', 'joseph.k@rojam.test', '$2y$10$06XpfORdRakd8tKNdkCRmu0XPmcNy5vrypokkeGvllH/zKIkjSnb2', '2026-08-18 12:08:46'),
(4, 'Mary', 'Wanjiku', '0734567890', 'mary.w@rojam.test', '$2y$10$O72IPv.Rdv5rvv0qgKNo3eEpOmTCtwfmvbuaPJzv7ZoEQP9/eM/6K', '2026-09-11 18:03:04'),
(5, 'Abbygail', 'Mwanduka', '0702436444', 'abbygail@rojam.com', '$2y$10$DCvLxUxjWdNbxmHGFaU9P.WmGEHphe5OSg24LFGfcLlgurlRHhX0y', '2026-09-12 06:51:16'),
(6, 'John', 'Paul', '0712309534', 'jpaul@gmail.com', '$2y$10$BlKiqg.Izypa1Zs1dDLQ5uvktrMgFC2XosYMGSWHlNyCE7OnEvg5C', '2026-09-12 10:53:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_status` enum('Pending','Processing','Ready for Pickup','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_status`, `total_amount`, `created_at`) VALUES
(1, 1, 'Completed', 11600.00, '2026-08-17 12:26:25'),
(2, 2, 'Completed', 12900.00, '2026-08-17 21:35:03'),
(3, 3, 'Completed', 25400.00, '2026-08-18 12:09:55'),
(4, 3, 'Completed', 43500.00, '2026-08-18 12:29:12'),
(5, 6, 'Completed', 16500.00, '2026-09-12 10:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 1, 4200.00, 4200.00),
(2, 1, 1, 1, 3500.00, 3500.00),
(3, 1, 2, 1, 3900.00, 3900.00),
(4, 2, 1, 1, 3500.00, 3500.00),
(5, 2, 6, 1, 2500.00, 2500.00),
(6, 2, 4, 1, 3000.00, 3000.00),
(7, 2, 2, 1, 3900.00, 3900.00),
(8, 3, 4, 2, 3000.00, 6000.00),
(9, 3, 3, 2, 4200.00, 8400.00),
(10, 3, 7, 2, 5500.00, 11000.00),
(11, 4, 8, 3, 6000.00, 18000.00),
(12, 4, 6, 3, 2500.00, 7500.00),
(13, 4, 8, 3, 6000.00, 18000.00),
(14, 5, 4, 1, 3000.00, 3000.00),
(15, 5, 6, 1, 2500.00, 2500.00),
(16, 5, 7, 2, 5500.00, 11000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(120) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `description`, `price`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 'Elegant Floral Dress', 1, 'Soft floral dress suitable for casual and semi-formal wear.', 3500.00, 10, '2026-08-17 11:18:10', '2026-08-17 21:35:03'),
(2, 'Weekend Maxi Dress', 1, 'Flowing maxi dress ideal for weekend outings.', 3900.00, 4, '2026-08-17 11:18:10', '2026-08-17 21:35:03'),
(3, 'Classic Denim Jacket', 2, 'Stylish denim jacket for layering and everyday wear.', 4200.00, 5, '2026-08-17 11:18:10', '2026-08-18 12:09:55'),
(4, 'Knitted Cardigan', 2, 'Warm cardigan in a neutral tone.', 3000.00, 5, '2026-08-17 11:18:10', '2026-09-12 10:54:59'),
(5, 'Office Blouse', 3, 'Comfortable blouse for work and smart casual outfits.', 1800.00, 15, '2026-08-17 11:18:10', '2026-08-17 11:18:10'),
(6, 'High Waist Trousers', 4, 'Smart high-waist trousers with a clean tailored finish.', 2500.00, 5, '2026-08-17 11:18:10', '2026-09-12 10:54:59'),
(7, 'Leather Ankle Boots', 5, 'Durable leather ankle boots for all-day wear.', 5500.00, 1, '2026-08-17 11:18:10', '2026-09-12 10:57:39'),
(8, 'hush puppies', 5, 'shoes', 6000.00, 0, '2026-08-18 12:28:09', '2026-09-11 19:06:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_customer` (`customer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `fk_order_details_order` (`order_id`),
  ADD KEY `fk_order_details_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_order_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

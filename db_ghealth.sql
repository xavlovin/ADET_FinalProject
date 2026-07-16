-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2026 at 10:18 AM
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
-- Database: `db_ghealth`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(6) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password`, `created_date`) VALUES
(1, 'admin@admin.com', 'admin123', '2026-07-05 14:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(6) UNSIGNED NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_email`, `action`, `timestamp`) VALUES
(1, 'admin@admin.com', 'Updated category: Fitness & Sports Nutritions', '2026-07-12 05:15:03'),
(2, 'admin@admin.com', 'Updated product: Neozep', '2026-07-12 05:15:51'),
(3, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 05:29:57'),
(4, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 05:48:07'),
(5, 'email@email.com', 'Updated product: bioflu', '2026-07-12 06:50:18'),
(6, 'email@email.com', 'Updated product: bioflu', '2026-07-12 06:50:52'),
(7, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 07:00:16'),
(8, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 08:31:09'),
(9, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 08:32:00'),
(10, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 08:51:50'),
(11, 'admin@admin.com', 'Updated product: bioflu', '2026-07-12 08:58:16'),
(12, 'admin@admin.com', 'Deleted category ID: 10', '2026-07-12 08:59:30'),
(13, 'admin@admin.com', 'Added category: Fitness & Sports Nutritions', '2026-07-12 09:01:16'),
(14, 'admin@admin.com', 'Added product: cadag', '2026-07-12 09:04:39'),
(15, 'admin@admin.com', 'Added product: franz', '2026-07-12 09:13:06'),
(16, 'admin@admin.com', 'Updated product: cadag', '2026-07-12 09:29:30'),
(17, 'admin@admin.com', 'Updated product: cadag', '2026-07-12 09:36:50'),
(18, 'admin@admin.com', 'Added product: ba', '2026-07-12 10:18:15'),
(19, 'admin@admin.com', 'Added product: sa', '2026-07-12 10:18:30'),
(20, 'admin@admin.com', 'Added product: ag', '2026-07-12 10:19:05'),
(21, 'admin@admin.com', 'Added product: au', '2026-07-12 10:19:30'),
(22, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 12:46:27'),
(23, 'admin@admin.com', 'Updated user: email@email.com', '2026-07-12 12:48:07'),
(24, 'admin@admin.com', 'Deleted product ID: 8', '2026-07-12 20:00:39'),
(25, 'admin@admin.com', 'Deleted product ID: 7', '2026-07-12 20:01:43'),
(26, 'admin@admin.com', 'Deleted product ID: 6', '2026-07-12 20:01:47'),
(27, 'admin@admin.com', 'Deleted product ID: 5', '2026-07-12 20:01:51'),
(28, 'admin@admin.com', 'Deleted product ID: 4', '2026-07-12 20:01:55'),
(29, 'admin@admin.com', 'Deleted product ID: 3', '2026-07-12 20:01:59'),
(30, 'admin@admin.com', 'Deleted product ID: 2', '2026-07-12 20:02:03'),
(31, 'admin@admin.com', 'Deleted product ID: 1', '2026-07-12 20:02:08'),
(32, 'admin@admin.com', 'Added product: Vitamin C', '2026-07-12 20:12:47'),
(33, 'admin@admin.com', 'Updated product: Vitamin C', '2026-07-12 20:16:13'),
(34, 'admin@admin.com', 'Added product: Zinc', '2026-07-12 20:17:01'),
(35, 'admin@admin.com', 'Added product: Echinacea', '2026-07-12 20:18:06'),
(36, 'admin@admin.com', 'Updated product: Vitamin C', '2026-07-12 20:36:15'),
(37, 'admin@admin.com', 'Updated product: Zinc', '2026-07-12 20:36:38'),
(38, 'admin@admin.com', 'Updated product: Echinacea', '2026-07-12 20:36:54'),
(39, 'admin@admin.com', 'Added product: Vitamin D', '2026-07-12 20:39:13'),
(40, 'admin@admin.com', 'Added product: Garlic Extract', '2026-07-12 20:41:07'),
(41, 'admin@admin.com', 'Added product: Asian Ginseng', '2026-07-12 20:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Immune Support', '🛡'),
(2, 'Daily Multivitamins', '💊'),
(3, 'Bone & Joint Health', '🦴'),
(4, 'Heart Health', '❤️'),
(5, 'Digestive Health', '🌱'),
(6, 'Brain & Memory', '🧠'),
(7, 'Energy & Vitality', '⚡'),
(8, 'Sleep & Relaxation', '💤'),
(9, 'Beauty & Skin Care', '✨'),
(12, 'Fitness & Sports Nutritions', '🏋');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(6) UNSIGNED NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `tagline` varchar(255) DEFAULT '',
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(10) NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_name`, `tagline`, `category`, `price`, `stock`, `image_file`, `added_date`) VALUES
(9, 'Vitamin C', 'Staple antioxidant for daily immune function.', 'Immune Support', 1250.00, 95, 'assets/img/products/1783908767_VitaminC.avif', '2026-07-13 02:12:47'),
(10, 'Zinc', 'Essential mineral for resisting daily infections.', 'Immune Support', 950.00, 45, 'assets/img/products/1783909021_Zinc.jpg', '2026-07-13 02:17:01'),
(11, 'Echinacea', 'Popular botanical herb supporting natural immunity.', 'Immune Support', 1100.00, 5, 'assets/img/products/1783909086_Echinacea.webp', '2026-07-13 02:18:06'),
(12, 'Vitamin D', 'Strengthens immunity to prevent chronic infections.', 'Immune Support', 850.00, 0, 'assets/img/products/1783910353_VitaminD.webp', '2026-07-13 02:39:13'),
(13, 'Garlic Extract', 'Natural supplement supporting daily immune function.', 'Immune Support', 750.00, 30, 'assets/img/products/1783910467_GarlicExtract.jpg', '2026-07-13 02:41:07'),
(14, 'Asian Ginseng', 'Herbal extract with immune-supporting properties.', 'Immune Support', 1400.00, 79, 'assets/img/products/1783910601_AsianGinseng.webp', '2026-07-13 02:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 2, 77.00, 'Pending', '2026-07-12 14:46:33'),
(2, 2, 18.00, 'Pending', '2026-07-12 14:56:15'),
(3, 2, 333.00, 'Pending', '2026-07-12 15:06:44'),
(4, 2, 114.00, 'Pending', '2026-07-12 15:40:42'),
(5, 2, 22.00, 'Pending', '2026-07-12 16:12:18'),
(6, 2, 22.00, 'Pending', '2026-07-12 16:12:43'),
(7, 2, 22.00, 'Pending', '2026-07-12 16:13:13'),
(8, 2, 343.00, 'Pending', '2026-07-12 16:21:24'),
(9, 2, 72.00, 'Pending', '2026-07-12 17:11:53'),
(10, 2, 94.00, 'Pending', '2026-07-12 17:12:58'),
(11, 2, 19.00, 'Pending', '2026-07-12 18:58:33'),
(12, 2, 17239.00, 'Pending', '2026-07-13 07:17:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 6, 2.00),
(2, 1, 2, 7, 7.00),
(3, 2, 1, 3, 2.00),
(4, 3, 3, 20, 15.00),
(5, 4, 1, 25, 2.00),
(6, 4, 3, 3, 15.00),
(7, 5, 4, 1, 9.00),
(8, 6, 4, 1, 9.00),
(9, 7, 4, 1, 9.00),
(10, 8, 2, 2, 7.00),
(11, 8, 5, 5, 3.00),
(12, 8, 7, 10, 12.00),
(13, 8, 8, 10, 16.00),
(14, 9, 2, 8, 7.00),
(15, 10, 2, 11, 7.00),
(16, 11, 2, 1, 7.00),
(17, 12, 11, 7, 1100.00),
(18, 12, 14, 6, 1400.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(6) UNSIGNED NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(50) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `accesslevel` varchar(50) DEFAULT 'user',
  `status` varchar(50) DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `address`, `contact`, `reg_date`, `accesslevel`, `status`, `profile_picture`) VALUES
(1, 'Prince Xavier Alcantara', 'test@test.com', '$2y$10$o1Ik4.kZteavjV4a9jhcz.boT7Ygznw4egWFfNiiOaUfXDqFJvWba', 'Test 123', '123', '2026-07-05 16:25:16', 'user', 'active', NULL),
(2, 'xavier', 'email@email.com', '$2y$10$bUltaH93lxhGZRqhMBoApOrySPrTYtHhi65.pUryO2.K8snz3tl3W', 'blk123', '09123456789', '2026-07-11 10:51:56', 'user', 'active', 'assets/img/uploads/1783866094_d2bb4da8-eb9a-465a-b314-c0d7223b2a0b.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

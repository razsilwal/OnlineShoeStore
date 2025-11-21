-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 21, 2025 at 07:01 PM
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
-- Database: `shoe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `password`) VALUES
(1, 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2'),
(2, 'raj', '2e8460f4b941efacdc6949646516b4f288b5b423'),
(4, 'krishna', 'e82fb456d6c596a3052b3fefefaf8b8d377722a18dfbbae597f488806910b9ba');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(10) NOT NULL,
  `quantity` int(10) NOT NULL,
  `image` varchar(100) NOT NULL,
  `size` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `pid`, `name`, `price`, `quantity`, `image`, `size`) VALUES
(1, 1, 1, 'Nike', 4000, 1, 'nike.jpg', ''),
(12, 1, 2, 'Heel', 2000, 1, 'photo.jpg', ''),
(13, 0, 0, '[value-4]', 0, 0, '[value-7]', ''),
(23, 5, 1, 'Nike', 4000, 1, 'nike.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `messages` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `number` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` date NOT NULL DEFAULT current_timestamp(),
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`, `transaction_id`) VALUES
(1, 2, 'Ivana Browning', '+1 (687) 4', 'bifil@mailinator.com', 'esewa', 'Provident eveniet  - Fugiat ipsum ipsum', 'Nike (4000 x 2) - ', 8000, '2025-08-10', 'completed', NULL),
(2, 4, 'Steel Fisher', '+1 (818) 2', 'butam@mailinator.com', 'esewa', 'Eum repellendus Lib - Rerum non consectetu', 'Formal Leather (1200 x 1) - ', 1200, '2025-08-11', 'pending', NULL),
(3, 5, 'Nevada Best', '+1 (605) 2', 'tidup@mailinator.com', 'esewa', 'Perferendis sit omn - Velit sint necessit', 'Heel (2000 x 2)', 4000, '2025-11-20', 'pending', NULL),
(4, 5, '', '', '', 'esewa', ' - ', 'Nike (4000 x 1), Heel (2000 x 1)', 6000, '2025-11-20', 'pending', NULL),
(7, 5, 'Tiger Robertson', '+1 (475) 2', 'fajamivi@mailinator.com', 'esewa', 'Ut consequatur Id  - Adipisci esse venia', 'Sneaker (3200 x 2)', 6400, '2025-11-20', 'completed', '691ec51841f88'),
(10, 5, 'Jacob Blankenship', '+1 (881) 9', 'novijyp@mailinator.com', 'cod', 'Facere non atque dol - Est veniam dolores', 'Formal Leather (1200 x 1)', 1200, '2025-11-20', 'completed', NULL),
(11, 5, 'Maile Houston', '+1 (952) 8', 'xulakexiwe@mailinator.com', 'esewa', 'Elit iusto quae id  - Sit enim ut ea aut ', '... (1000 x 1)', 1000, '2025-11-20', 'completed', 'txn_691f59cec0c4f'),
(13, 5, 'Rhona Parks', '+1 (817) 6', 'boqumac@mailinator.com', 'esewa', 'Ut earum veniam aut - Architecto excepteur', '... (1000 x 1)', 1000, '2025-11-21', 'failed', '691f5c36b0adc'),
(15, 5, 'Anjolie Mendoza', '+1 (194) 8', 'weha@mailinator.com', 'esewa', 'Elit id consectetur - Odio laborum Labore', '... (1000 x 1)', 1000, '2025-11-21', 'failed', '691f5ef4efb12'),
(16, 5, 'Cooper Thomas', '+1 (478) 9', 'kasutyvu@mailinator.com', 'esewa', 'Reprehenderit nihil  - In dolores veniam N', '... (1000 x 1)', 1000, '2025-11-21', 'completed', '691f61e68850f'),
(17, 5, 'Martha Brooks', '+1 (631) 6', 'guwybaluk@mailinator.com', 'esewa', 'Laudantium repudian - Sunt dolor aliqua ', 'Heel (2000 x 2)', 4000, '2025-11-21', 'completed', '691fde7e87a4b'),
(18, 5, 'Donna Lynch', '+1 (765) 2', 'cokyx@mailinator.com', 'esewa', 'Ut dolores do earum  - Fugiat ea sed volup', 'Formal Leather (1200 x 2)', 2400, '2025-11-21', 'completed', '691fe19beec7e'),
(19, 14, 'Guinevere Robinson', '+1 (304) 8', 'mekuxo@mailinator.com', 'cod', 'Consequat Quis volu - Eius animi ea iure ', 'Nike (4000 x 3)', 12000, '2025-11-21', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `details` varchar(500) NOT NULL,
  `price` int(10) NOT NULL,
  `image_01` varchar(100) NOT NULL,
  `image_02` varchar(100) NOT NULL,
  `image_03` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `details`, `price`, `image_01`, `image_02`, `image_03`) VALUES
(1, 'Nike', 'Our Trending shoes  which is better and comfortable.', 4000, 'nike.jpg', 'nike.jpg', 'nike.jpg'),
(2, 'Heel', 'This heel make you feel confident and sexy.', 2000, 'photo.jpg', 'photo.jpg', 'photo.jpg'),
(3, 'Sneaker', 'Best sneaker in the market ', 3200, 'nike5.jpg', '', ''),
(4, 'Formal Leather', 'visit party, school, offices using this shoes ', 1200, 'formal1.jpg', '', ''),
(5, '..', 'edsfasdff', 1000, '8th Semester Syllabus.pdf', '', ''),
(6, '...', 'dsgfgfg', 1000, 'nike5.jpg', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `otp_expiry` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `otp`, `otp_expiry`) VALUES
(1, 'raj', 'raj@gmail.com', '563992725', '', 0),
(4, 'krishna', 'ka44698820@gmail.com', '563992725', '', 0),
(5, 'raj21', 'raj1@gmail.com', '563992725', '', 0),
(6, 'kisne', 'kisna1@gmail.com', '563992725', '', 0),
(7, 'ram', 'ram@gmail.com', '1248358', '', 0),
(14, 'sabal', 'sabalsilwal51@gmail.com', 'e82fb456d6c596a3052b3fefefaf8b8d377722a18dfbbae597f488806910b9ba', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

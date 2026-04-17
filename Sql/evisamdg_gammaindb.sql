-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 08, 2026 at 07:10 AM
-- Server version: 10.11.15-MariaDB
-- PHP Version: 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evisamdg_gammaindb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$HhWcrYpBtdJ9RX41lm6z4O6bvSQw8dMVgESLgFmmJgPpQJyhPdyqq');

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `method`, `amount`, `transaction_id`, `status`, `created_at`) VALUES
(1, 1, 'bKash', 100.00, '4663', 'Approved', '2026-02-07 14:36:58'),
(2, 1, 'bKash', 100.00, 'dfsdvvdav', 'Approved', '2026-02-08 05:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `result` enum('Win','Loss') NOT NULL,
  `amount` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_history`
--

CREATE TABLE `match_history` (
  `id` int(11) NOT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `played_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `msg` varchar(255) NOT NULL,
  `type` varchar(20) DEFAULT 'info',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `msg`, `type`, `created_at`) VALUES
(1, 1, 'Hi,Developer-Imtiaz', 'info', '2026-02-07 09:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `referral_bonus` decimal(10,2) DEFAULT 50.00,
  `signup_bonus` decimal(10,2) DEFAULT 0.00,
  `min_withdraw` decimal(10,2) DEFAULT 500.00,
  `bkash_number` varchar(20) DEFAULT '',
  `nagad_number` varchar(20) DEFAULT '',
  `rocket_number` varchar(20) DEFAULT '',
  `contact_link` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `referral_bonus`, `signup_bonus`, `min_withdraw`, `bkash_number`, `nagad_number`, `rocket_number`, `contact_link`) VALUES
(1, 30.00, 15.00, 40.00, '01812816940', '01812816940', '01812816940', '');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT 'tournaments.php',
  `bg_color` varchar(50) DEFAULT 'from-violet-600 to-indigo-600',
  `btn_text` varchar(50) DEFAULT 'Join Now',
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `entry_fee` decimal(10,2) NOT NULL,
  `prize_pool` decimal(10,2) NOT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `status` enum('open','live','completed') DEFAULT 'open',
  `max_players` int(11) DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `title`, `entry_fee`, `prize_pool`, `start_time`, `status`, `max_players`) VALUES
(21, 'Imtiaz-Test', 10.00, 100.00, '2026-02-08T13:09', 'open', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tournament_participants`
--

CREATE TABLE `tournament_participants` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `joined_at` timestamp NULL DEFAULT current_timestamp(),
  `color` varchar(20) DEFAULT 'green'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('deposit','withdraw') NOT NULL,
  `status` varchar(20) DEFAULT 'Success',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `title`, `amount`, `type`, `status`, `created_at`) VALUES
(1, 1, 'Welcome Bonus', 10.00, 'deposit', 'Success', '2026-02-07 14:29:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `referral_earnings` decimal(10,2) DEFAULT 0.00,
  `password` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT 1000.00,
  `avatar` varchar(255) DEFAULT 'default.png',
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `country` varchar(5) DEFAULT 'BD',
  `level` int(11) DEFAULT 1,
  `xp` int(11) DEFAULT 0,
  `bonus_balance` decimal(10,2) DEFAULT 50.00,
  `win_balance` decimal(10,2) DEFAULT 0.00,
  `fair_play_score` int(11) DEFAULT 100,
  `is_online` tinyint(1) DEFAULT 0,
  `settings_sound` tinyint(1) DEFAULT 1,
  `settings_anim` tinyint(1) DEFAULT 1,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `referral_code`, `referred_by`, `email`, `referral_earnings`, `password`, `balance`, `avatar`, `wins`, `losses`, `created_at`, `country`, `level`, `xp`, `bonus_balance`, `win_balance`, `fair_play_score`, `is_online`, `settings_sound`, `settings_anim`, `phone`) VALUES
(1, 'developerimtiaz', 'DEV9599', NULL, 'developerimtiaz75@gmail.com', 0.00, '$2y$10$8HY7XjUwB0QVKDDhuwSLL.1qfLdCa/EbRqC732fmZsNu2fnEDihRO', 2821.00, 'user_1_1770471156.jpg', 0, 0, '2026-02-07 05:16:40', 'BD', 1, 0, 50.00, 0.00, 100, 0, 1, 1, '01812816940'),
(2, 'mdimtiaz123', NULL, NULL, 'imtiaz@gmail.com', 0.00, '$2y$10$P5pO4Mt0N1bE3VKFkSPijOxBzwjVgQ667wyfFSCCHMJIdpMXeV6ua', 750.00, 'user_2_1770484450.jpg', 0, 0, '2026-02-07 10:56:02', 'BD', 1, 0, 50.00, 0.00, 100, 0, 1, 1, NULL),
(3, 'gfxleader', NULL, NULL, 'gfxdeveloperparvez@gmail.com', 0.00, '$2y$10$SaJGE2bSfAfy7kKJUl2RKOjlpHxJf2Rv3y8NH0UE26HGk6PIzFG3.', 290.00, 'default.png', 0, 0, '2026-02-07 14:03:58', 'BD', 1, 0, 50.00, 0.00, 100, 0, 1, 1, NULL),
(4, 'mdshadin', NULL, NULL, 'shadin@gmail.com', 0.00, '$2y$10$6xzQj4k56IrPt3eWomExROHCByV2rCZ7G6qfuWwzETFDas0cUQPWa', 80.00, 'default.png', 0, 0, '2026-02-07 18:48:52', 'BD', 1, 0, 50.00, 0.00, 100, 0, 1, 1, '01724457403');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `amount`, `type`, `description`, `created_at`) VALUES
(1, 1, 10.00, 'deduct', 'Joined Tournament #5', '2026-02-07 09:45:12'),
(2, 1, 19.00, 'deduct', 'Joined Tournament: Imtiaz-2', '2026-02-07 09:48:47'),
(3, 2, 10.00, 'deduct', 'Joined Match: New-Match', '2026-02-07 10:57:10'),
(4, 1, 10.00, 'deduct', 'Joined Match: New-Match', '2026-02-07 10:57:44'),
(5, 1, 20.00, 'deduct', 'Joined Match: Imtiaz-2', '2026-02-07 12:46:19'),
(6, 2, 20.00, 'deduct', 'Joined Match: Imtiaz-2', '2026-02-07 12:46:56'),
(7, 3, 10.00, 'deduct', 'Joined Match: Imtiaz', '2026-02-07 14:05:44'),
(8, 3, 0.00, 'deduct', 'Joined Match: IPR', '2026-02-07 14:06:08'),
(9, 1, 0.00, 'deduct', 'Joined Match: IPR', '2026-02-07 14:06:32'),
(10, 2, 10.00, 'deduct', 'Joined Match: IT-MATCH', '2026-02-07 17:14:55'),
(11, 4, 10.00, 'debit', 'Joined Tournament #18', '2026-02-08 04:49:35'),
(12, 1, 10.00, 'debit', 'Joined Tournament #18', '2026-02-08 04:49:58'),
(13, 4, 10.00, 'debit', 'Joined Tournament #17', '2026-02-08 04:55:02'),
(14, 1, 10.00, 'debit', 'Joined Tournament #17', '2026-02-08 04:55:10'),
(15, 1, 100.00, 'deposit', 'Deposit Approved', '2026-02-08 05:10:50');

-- --------------------------------------------------------

--
-- Table structure for table `withdraws`
--

CREATE TABLE `withdraws` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `withdraws`
--

INSERT INTO `withdraws` (`id`, `user_id`, `method`, `amount`, `account_number`, `status`, `created_at`) VALUES
(1, 2, 'bKash', 100.00, '01812816940', 'Approved', '2026-02-07 17:19:30'),
(2, 2, 'bKash', 100.00, '01812816940', 'Approved', '2026-02-07 17:19:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `match_history`
--
ALTER TABLE `match_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdraws`
--
ALTER TABLE `withdraws`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_history`
--
ALTER TABLE `match_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `withdraws`
--
ALTER TABLE `withdraws`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD CONSTRAINT `tournament_participants_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`),
  ADD CONSTRAINT `tournament_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

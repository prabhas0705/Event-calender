-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 07:17 PM
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
-- Database: `event_calendar`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#3788d8'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `color`) VALUES
(1, 'General', '#3788d8'),
(2, 'Meeting', '#2ecc71'),
(3, 'Appointment', '#e74c3c'),
(4, 'Task', '#f1c40f'),
(5, 'Personal', '#9b59b6'),
(8, 'Birthday', '#e74c3c'),
(9, 'Holiday', '#f1c40f'),
(10, 'Other', '#95a5a6');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT 'General',
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurrence_pattern` varchar(255) DEFAULT NULL,
  `recurrence_end_date` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `created_at`, `category`, `is_recurring`, `recurrence_pattern`, `recurrence_end_date`, `user_id`) VALUES
(10, 'Vamsi ', 'WFWSA', '2025-04-15 14:00:00', '2025-04-15 18:15:00', '2025-04-15 15:33:38', 'Meeting', 0, NULL, NULL, NULL),
(11, 'wfwqwf', 'wqwfwqf', '2025-04-15 21:15:00', '2025-04-15 22:15:00', '2025-04-15 15:36:47', 'General', 0, NULL, NULL, NULL),
(12, 'wfwqf', 'wwfwf', '2025-04-16 21:15:00', '2025-04-16 22:15:00', '2025-04-15 15:36:55', 'General', 0, NULL, NULL, NULL),
(14, 'india ipl', 'indian premir league', '2025-04-15 21:15:00', '2025-04-15 22:15:00', '2025-04-15 15:43:55', 'General', 0, NULL, NULL, NULL),
(16, 'this is', 'a event', '2025-04-15 21:30:00', '2025-04-15 22:30:00', '2025-04-15 15:47:57', 'Appointment', 0, NULL, NULL, NULL),
(17, 'ascsa', 'this is for bdays ', '2025-04-15 22:30:00', '2025-04-15 23:30:00', '2025-04-15 16:55:06', 'Birthday', 1, 'daily', '0000-00-00 00:00:00', 3),
(18, 'christamans', 'public holiday', '2025-04-22 22:30:00', '2025-04-23 23:30:00', '2025-04-15 16:55:30', 'Holiday', 1, 'daily', '0000-00-00 00:00:00', 3),
(19, 'idiot movie', 'mobbie is lide', '2025-04-29 22:30:00', '2025-04-29 23:30:00', '2025-04-15 16:56:49', 'Other', 1, 'daily', '0000-00-00 00:00:00', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `notification_lead_time` int(11) DEFAULT 24,
  `last_notification_sent` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_preferences`
--

INSERT INTO `notification_preferences` (`id`, `user_id`, `email_notifications`, `notification_lead_time`, `last_notification_sent`) VALUES
(1, 3, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '2025-04-15 15:59:39', '2025-04-15 15:59:39'),
(2, 'prabhas', 'prabhas@gmail.com', '$2y$10$b5daxGt1bGVsCszDNa4OkeZJqVY2qKjSZyLPSRV/c.7OvMYJivdi2', 'Prabhas chirala', '2025-04-15 16:02:04', '2025-04-15 16:02:04'),
(3, 'sieva', 'sieva@gmail.com', '$2y$10$HHvDw.EuNdjGCpbUBgAW/OmhUTS.ZJPNDQtMObQhWHVhPYJbWYnwa', NULL, '2025-04-15 16:52:08', '2025-04-15 16:52:08'),
(4, 'saranya', 'saranya@gmail.com', '$2y$10$BmWS75ptpJmup5UzdFwGSumK/bcb77J887iwSa0IhPnItVy/02h52', NULL, '2025-04-15 16:56:24', '2025-04-15 16:56:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Table for recurring events
CREATE TABLE IF NOT EXISTS `recurring_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for recurring events meta data
CREATE TABLE IF NOT EXISTS `recurring_events_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `recurring_events_meta_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

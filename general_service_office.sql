-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2025 at 06:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `general_service_office`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `borrowing_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `admin_issued_id` int(11) DEFAULT NULL,
  `admin_received_id` int(11) DEFAULT NULL,
  `status` enum('active','returned','overdue','lost') DEFAULT 'active',
  `condition_on_return` enum('excellent','good','fair','poor','damaged') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','denied') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `return_notes` text DEFAULT NULL,
  `returned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`borrowing_id`, `equipment_id`, `user_id`, `borrow_date`, `due_date`, `return_date`, `admin_issued_id`, `admin_received_id`, `status`, `condition_on_return`, `notes`, `created_at`, `updated_at`, `approval_status`, `approved_by`, `approval_date`, `admin_notes`, `return_notes`, `returned_by`) VALUES
(2, 2, 3, '2025-04-20 12:52:00', '2025-04-27 09:52:00', NULL, NULL, NULL, '', NULL, 'aasda', '2025-04-20 09:53:19', '2025-04-20 09:53:19', 'pending', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Power Tools', 'Electric and battery-powered tools for construction and maintenance', '2025-04-19 10:34:38'),
(2, 'Sound Systems', 'Audio equipment including speakers, mixers, and microphones', '2025-04-19 10:34:38'),
(3, 'Electronics', 'Electronic devices like projectors, cameras, and computers', '2025-04-19 10:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `equipment_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `status` enum('available','maintenance','retired') NOT NULL,
  `condition_status` enum('new','good','fair','poor') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `equipment_code`, `name`, `category_id`, `description`, `acquisition_date`, `status`, `condition_status`, `notes`, `created_at`, `updated_at`) VALUES
(2, '001', 'Microphone', 3, '', '2025-04-20', 'maintenance', 'new', NULL, '2025-04-20 06:41:36', '2025-04-20 10:19:07');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `maintenance_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `maintenance_date` date NOT NULL,
  `resolved_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `resolved_by` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','staff','admin') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `role`, `department`, `phone`, `created_at`, `updated_at`) VALUES
(2, 'Jm', 'Peligro', 'jmpeligro@plpasig.edu.ph', '$2y$10$mPiM2WhC6H16sW3.pJp3X.7IUbnSo9LrUSmHpJAflC9cRcEdj7q5e', 'student', 'College of Computer Studies', '12144121', '2025-04-19 10:49:33', '2025-04-19 10:49:33'),
(3, 'Bryan', 'Bermudez', 'bry@plpasig.edu.ph', '$2y$10$HUveH9O4JkwOUZFsqCqX3OeB4itZZSsHDdS1CoJe4SR7swahMdPKG', 'staff', 'General Service Office', '1234567890', '2025-04-19 10:54:45', '2025-04-19 10:54:45'),
(5, 'Jm', 'Peligro', 'jm@plpasig.edu', '$2y$10$znGJ8Iw7yKxySbCb4pIWm.yxYocjMWewEEtxuRWAstoL57sNSlIHa', 'admin', 'College of Computer Studies', '12345678', '2025-04-19 11:20:26', '2025-04-19 11:20:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`borrowing_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_issued_id` (`admin_issued_id`),
  ADD KEY `admin_received_id` (`admin_received_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD UNIQUE KEY `equipment_code` (`equipment_code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `borrowing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_3` FOREIGN KEY (`admin_issued_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_4` FOREIGN KEY (`admin_received_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

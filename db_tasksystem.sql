-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 14, 2025 at 04:48 PM
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
-- Database: `db_tasksystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `deadline` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `deadline`, `status`, `created_at`) VALUES
(1, 'Debuggig', 'Debug the current system that is in development', 4, 1, '2025-07-21', 'Pending', '2025-07-13 16:00:04'),
(2, 'Styling Frond End', 'Use of CSS, Bootstrap', 4, 1, '2025-07-31', 'Pending', '2025-07-13 17:18:40'),
(3, 'Testing', 'Test the system. Use PEN-TEST.', 4, 1, '2025-07-30', 'Pending', '2025-07-13 17:21:31'),
(4, 'Installation', 'System installation.', 4, 1, '2025-07-31', 'In Progress', '2025-07-13 17:23:34'),
(5, 'Database intergration', 'MYSQL and MongoDB', 4, 1, '2025-07-31', 'Pending', '2025-07-14 09:45:38'),
(6, 'Database intergration', 'MYSQL and MongoDB', 4, 1, '2025-07-31', 'Pending', '2025-07-14 09:49:37'),
(7, 'System Design', 'Both Fronted and BAckend', 8, 1, '2025-07-30', 'In Progress', '2025-07-14 09:53:25'),
(8, 'Mobile App Testing', 'Test Mobile application', 8, 1, '2025-07-30', 'Completed', '2025-07-14 12:17:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_changed` tinyint(1) DEFAULT 0,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_notifications` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `password_changed`, `role`, `created_at`, `email_notifications`) VALUES
(1, 'Admin', 'admin@test.com', '$2y$10$uy/LTTjwsAAJN9MR8sEUhetsteqzZjrEar8x3lW2M9nhQAdnepsHe', 1, 'admin', '2025-07-13 15:30:49', 1),
(4, 'Alvin', 'wachilongaalvin@gmail.com', '$2y$10$//zRNvZiHnb37g6rKTY8sOqGwVX4IOJI91NzMA8Ar5yTF9fPiZV4y', 1, 'user', '2025-07-13 18:06:54', 1),
(8, 'wasike', 'jameswekesa002@gmail.com', '$2y$10$HUumzbvGyCTD1CkIwHrT8O3KhB9jNhq6y94N7jW4tnkZ7NYMkPyRy', 1, 'user', '2025-07-14 09:51:10', 1),
(20, 'Nyongesa', '1036637@cuea.edu', '$2y$10$0MIGtiOSRWx/HyJyO76PtesT5XXPpTrp9NJnAxgMvNGhQUjZH/UgC', 1, 'user', '2025-07-14 14:29:30', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

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
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

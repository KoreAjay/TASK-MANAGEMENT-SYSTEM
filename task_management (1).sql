-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 02:02 PM
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
-- Database: `task_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `action_type`, `description`, `timestamp`) VALUES
(1, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:46:20'),
(2, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:48:06'),
(3, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:48:28'),
(4, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:50:35'),
(5, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:50:49'),
(6, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:56:06'),
(7, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:56:34'),
(8, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:57:07'),
(9, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:57:33'),
(10, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:57:40'),
(11, 1, 'Admin Dashboard Access', 'Admin admin accessed the dashboard.', '2025-07-02 11:57:47');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'IT', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(2, 'HR', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(3, 'Marketing', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` varchar(36) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `deadline_date` date NOT NULL,
  `assigned_by_user_id` int(11) NOT NULL,
  `assigned_to_user_id` int(11) DEFAULT NULL,
  `current_department_id` int(11) NOT NULL,
  `priority` enum('Low','Medium','High') NOT NULL DEFAULT 'Medium',
  `status` enum('Pending','In Progress','Completed','Rejected','Transferred') NOT NULL DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_name`, `description`, `category_id`, `start_date`, `deadline_date`, `assigned_by_user_id`, `assigned_to_user_id`, `current_department_id`, `priority`, `status`, `created_at`, `updated_at`) VALUES
('task_6865008760d741.35352362', 'Task1', 'Modules ', 1, '2025-07-02', '2025-07-04', 2, 3, 3, 'Medium', 'Transferred', '2025-07-02 15:18:55', '2025-07-02 15:22:58'),
('task_686509b07b1af2.22248604', 'Task2', 'task2', 2, '2025-07-02', '2025-07-05', 3, NULL, 1, 'Low', 'Rejected', '2025-07-02 15:58:00', '2025-07-02 15:59:07'),
('task_68650a5a78c340.85462240', 'Task3', 'Task3', 1, '2025-07-02', '2025-07-04', 1, 2, 1, 'Medium', 'Completed', '2025-07-02 16:00:50', '2025-07-02 16:09:36'),
('task_68650cf92dd183.74571401', 'Task4', 'Task4', 3, '2025-07-02', '2025-07-04', 1, 12, 1, 'High', 'Transferred', '2025-07-02 16:12:01', '2025-07-02 16:13:57'),
('task_6865102d108b16.21505530', 'Task5', 'Task5', 1, '2025-07-02', '2025-07-05', 12, 3, 3, 'Low', 'Completed', '2025-07-02 16:25:41', '2025-07-02 16:29:37'),
('task_686512932b8e75.63885023', 'Task6', 'Task6', 1, '2025-07-02', '2025-07-06', 1, 15, 2, 'High', 'Completed', '2025-07-02 16:35:55', '2025-07-02 16:53:38'),
('task_68651296588cd6.10483182', 'Task6', 'Task6', 1, '2025-07-02', '2025-07-06', 1, NULL, 1, 'High', 'Rejected', '2025-07-02 16:35:58', '2025-07-02 16:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `attachment_id` int(11) NOT NULL,
  `task_id` varchar(36) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size_kb` int(11) DEFAULT NULL,
  `uploaded_by_user_id` int(11) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_attachments`
--

INSERT INTO `task_attachments` (`attachment_id`, `task_id`, `file_name`, `file_path`, `file_type`, `file_size_kb`, `uploaded_by_user_id`, `uploaded_at`) VALUES
(1, 'task_6865008760d741.35352362', 'JS4 ClassNotes.pdf', 'path/to/uploads/JS4 ClassNotes.pdf', 'application/pdf', 101, 2, '2025-07-02 15:18:55'),
(2, 'task_686509b07b1af2.22248604', 'travel_db (5).sql', 'path/to/uploads/travel_db (5).sql', 'application/octet-stream', 25, 3, '2025-07-02 15:58:00'),
(3, 'task_68650a5a78c340.85462240', 'users.sql', 'path/to/uploads/users.sql', 'application/octet-stream', 2, 1, '2025-07-02 16:00:50'),
(4, 'task_68650cf92dd183.74571401', 'travel_db (8).sql', 'path/to/uploads/travel_db (8).sql', 'application/octet-stream', 26, 1, '2025-07-02 16:12:01'),
(5, 'task_6865102d108b16.21505530', 'users.sql', 'path/to/uploads/users.sql', 'application/octet-stream', 2, 12, '2025-07-02 16:25:41'),
(6, 'task_686512932b8e75.63885023', 'users.sql', 'path/to/uploads/users.sql', 'application/octet-stream', 2, 1, '2025-07-02 16:35:55'),
(7, 'task_68651296588cd6.10483182', 'users.sql', 'path/to/uploads/users.sql', 'application/octet-stream', 2, 1, '2025-07-02 16:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `task_categories`
--

CREATE TABLE `task_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_categories`
--

INSERT INTO `task_categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Development', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(2, 'Support', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(3, 'Marketing', NULL, '2025-07-02 15:05:58', '2025-07-02 15:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `task_rejections`
--

CREATE TABLE `task_rejections` (
  `rejection_id` int(11) NOT NULL,
  `task_id` varchar(36) NOT NULL,
  `rejected_by_user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `rejected_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_rejections`
--

INSERT INTO `task_rejections` (`rejection_id`, `task_id`, `rejected_by_user_id`, `reason`, `rejected_at`) VALUES
(1, 'task_686509b07b1af2.22248604', 12, 'issue', '2025-07-02 15:59:07'),
(2, 'task_68651296588cd6.10483182', 2, 'doc', '2025-07-02 16:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `task_transfers`
--

CREATE TABLE `task_transfers` (
  `transfer_id` int(11) NOT NULL,
  `task_id` varchar(36) NOT NULL,
  `transferred_by_user_id` int(11) NOT NULL,
  `transferred_from_user_id` int(11) DEFAULT NULL,
  `transferred_to_user_id` int(11) NOT NULL,
  `transferred_from_department_id` int(11) DEFAULT NULL,
  `transferred_to_department_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `transferred_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_transfers`
--

INSERT INTO `task_transfers` (`transfer_id`, `task_id`, `transferred_by_user_id`, `transferred_from_user_id`, `transferred_to_user_id`, `transferred_from_department_id`, `transferred_to_department_id`, `reason`, `transferred_at`) VALUES
(1, 'task_6865008760d741.35352362', 2, 2, 3, 1, 3, 'Personal Issue', '2025-07-02 15:22:58'),
(2, 'task_68650cf92dd183.74571401', 16, 16, 12, 3, 1, 'not solved', '2025-07-02 16:13:57'),
(3, 'task_6865102d108b16.21505530', 2, 2, 13, 1, 1, 'issue not solved', '2025-07-02 16:27:59'),
(4, 'task_6865102d108b16.21505530', 13, 13, 3, 1, 3, 'approve', '2025-07-02 16:29:09'),
(5, 'task_686512932b8e75.63885023', 2, 2, 13, 1, 1, 'trnsfer', '2025-07-02 16:47:42'),
(6, 'task_686512932b8e75.63885023', 13, 13, 14, 1, 2, 'approve ', '2025-07-02 16:48:42'),
(7, 'task_686512932b8e75.63885023', 14, 14, 15, 2, 2, 'approve', '2025-07-02 16:52:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `department_id`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'adminpass', 'admin@example.com', 'Admin User', 1, 'admin', 1, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(2, 'john.doe', 'johnpass', 'john.doe@example.com', 'John Doe', 1, 'user', 1, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(3, 'jane.smith', 'janepass', 'jane.smith@example.com', 'Jane Smith', 3, 'user', 1, '2025-07-02 15:05:58', '2025-07-02 15:05:58'),
(12, 'mike.tech', 'mikepass', 'mike.tech@example.com', 'Mike Tech Lead', 1, 'user', 1, '2025-07-02 15:57:03', '2025-07-02 15:57:03'),
(13, 'olivia.support', 'oliviapass', 'olivia.support@example.com', 'Olivia IT Support', 1, 'user', 1, '2025-07-02 15:57:03', '2025-07-02 15:57:03'),
(14, 'peter.hr', 'peterpass', 'peter.hr@example.com', 'Peter HR Manager', 2, 'user', 1, '2025-07-02 15:57:03', '2025-07-02 15:57:03'),
(15, 'quinn.recruit', 'quinnpass', 'quinn.recruit@example.com', 'Quinn Talent Sourcer', 2, 'user', 1, '2025-07-02 15:57:03', '2025-07-02 15:57:03'),
(16, 'rachel.mkt', 'rachelpass', 'rachel.mkt@example.com', 'Rachel Marketing Strategist', 3, 'user', 1, '2025-07-02 15:57:03', '2025-07-02 15:57:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `assigned_by_user_id` (`assigned_by_user_id`),
  ADD KEY `idx_tasks_assigned_to_user_id` (`assigned_to_user_id`),
  ADD KEY `idx_tasks_current_department_id` (`current_department_id`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_deadline_date` (`deadline_date`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `uploaded_by_user_id` (`uploaded_by_user_id`),
  ADD KEY `idx_task_attachments_task_id` (`task_id`);

--
-- Indexes for table `task_categories`
--
ALTER TABLE `task_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `task_rejections`
--
ALTER TABLE `task_rejections`
  ADD PRIMARY KEY (`rejection_id`),
  ADD KEY `rejected_by_user_id` (`rejected_by_user_id`),
  ADD KEY `idx_task_rejections_task_id` (`task_id`);

--
-- Indexes for table `task_transfers`
--
ALTER TABLE `task_transfers`
  ADD PRIMARY KEY (`transfer_id`),
  ADD KEY `transferred_by_user_id` (`transferred_by_user_id`),
  ADD KEY `transferred_from_user_id` (`transferred_from_user_id`),
  ADD KEY `transferred_to_user_id` (`transferred_to_user_id`),
  ADD KEY `transferred_from_department_id` (`transferred_from_department_id`),
  ADD KEY `transferred_to_department_id` (`transferred_to_department_id`),
  ADD KEY `idx_task_transfers_task_id` (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `task_categories`
--
ALTER TABLE `task_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_rejections`
--
ALTER TABLE `task_rejections`
  MODIFY `rejection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_transfers`
--
ALTER TABLE `task_transfers`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `task_categories` (`category_id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`current_department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`),
  ADD CONSTRAINT `task_attachments_ibfk_2` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `task_rejections`
--
ALTER TABLE `task_rejections`
  ADD CONSTRAINT `task_rejections_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`),
  ADD CONSTRAINT `task_rejections_ibfk_2` FOREIGN KEY (`rejected_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `task_transfers`
--
ALTER TABLE `task_transfers`
  ADD CONSTRAINT `task_transfers_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`),
  ADD CONSTRAINT `task_transfers_ibfk_2` FOREIGN KEY (`transferred_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `task_transfers_ibfk_3` FOREIGN KEY (`transferred_from_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `task_transfers_ibfk_4` FOREIGN KEY (`transferred_to_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `task_transfers_ibfk_5` FOREIGN KEY (`transferred_from_department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `task_transfers_ibfk_6` FOREIGN KEY (`transferred_to_department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

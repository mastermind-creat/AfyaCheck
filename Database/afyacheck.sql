-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 23, 2025 at 08:02 PM
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
-- Database: `afyacheck`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `fullname`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'Tracy', 'admin@afyacheck.com', '0712345678', '$2y$10$zhbDrgWLPnkI0/UON8sek.8ffyKeUsK3L/Pfg.KhFf1y1tWehq7iK', '2025-08-19 11:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `systolic` int(11) DEFAULT NULL,
  `diastolic` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `patient_id`, `message`, `created_at`, `systolic`, `diastolic`, `status`) VALUES
(1, 1, 'Critical BP reading: Systolic 140, Diastolic 134 at 2025-08-18T13:45 [Critical]', '2025-08-18 13:45:00', NULL, NULL, 'read'),
(2, 3, 'Critical BP reading: Systolic 185, Diastolic 124 at 2025-08-22 16:00 [Critical]', '2025-08-22 16:00:00', 185, 124, 'read');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `provider` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `reminder` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `patient_id`, `type`, `date`, `time`, `provider`, `notes`, `reminder`, `created_at`) VALUES
(1, 1, 2, 'Follow-up', '2025-08-21', '08:00:00', 'Kombewa', '', 0, '2025-08-20 19:05:46'),
(2, 1, 1, 'Medication Review', '2025-08-20', '23:00:00', 'Tracy Odhiambo', '', 0, '2025-08-20 19:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `doctor_id`, `patient_id`, `assigned_at`) VALUES
(1, 1, 1, '2025-08-19 15:48:50'),
(2, 1, 2, '2025-08-19 22:43:32'),
(3, 2, 3, '2025-08-22 13:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `bp_readings`
--

CREATE TABLE `bp_readings` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `systolic` int(11) NOT NULL,
  `diastolic` int(11) NOT NULL,
  `pulse` int(11) NOT NULL,
  `reading_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `doctor_comment` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bp_readings`
--

INSERT INTO `bp_readings` (`id`, `patient_id`, `systolic`, `diastolic`, `pulse`, `reading_time`, `created_at`, `doctor_comment`, `status`) VALUES
(1, 1, 120, 76, 0, '2025-08-19 12:12:00', '2025-08-19 11:00:21', 'need more therapy', 'Normal'),
(2, 1, 139, 73, 0, '2025-08-18 10:00:00', '2025-08-19 11:02:39', NULL, 'Normal'),
(3, 1, 120, 77, 0, '2025-08-19 04:06:00', '2025-08-19 13:35:23', NULL, 'Normal'),
(4, 1, 123, 99, 0, '2025-08-19 12:34:00', '2025-08-19 14:23:30', NULL, 'Normal'),
(5, 1, 78, 105, 0, '2025-08-19 13:00:00', '2025-08-19 14:25:43', NULL, 'Normal'),
(6, 1, 140, 134, 0, '2025-08-18 13:45:00', '2025-08-19 14:31:01', NULL, 'Critical'),
(7, 2, 121, 77, 0, '2025-08-19 19:00:00', '2025-08-19 19:42:17', 'nice one', 'Normal'),
(8, 2, 120, 75, 35, '2025-08-20 22:00:00', '2025-08-20 18:54:02', '', 'Normal'),
(9, 3, 129, 89, 56, '2025-07-09 11:02:00', '2025-08-22 08:19:17', '', 'Normal'),
(10, 3, 138, 120, 67, '2025-08-22 12:00:00', '2025-08-22 10:34:32', '', 'Stage 2 Hypertension'),
(11, 3, 80, 120, 60, '2025-08-22 13:00:00', '2025-08-22 10:35:28', '', 'Stage 2 Hypertension'),
(12, 3, 181, 123, 69, '2025-08-22 13:45:00', '2025-08-22 10:36:26', 'Do more exercise', 'Critical'),
(13, 3, 185, 124, 66, '2025-08-22 16:00:00', '2025-08-22 13:53:40', '', 'Critical');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `fullname`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'Tracy Odhiambo', 'tracy@afyacheck.com', '0786545342', '$2y$10$n6cQqDbq3E8WytCJvit/g.kaP81kUE.ZvluBdUAqj2oa9mQM/4mHe', '2025-08-19 11:19:51'),
(2, 'Mary Jane', 'mary@afyacheck.com', '0784657645', '$2y$10$JRjJqxaPQQxS8FX58WCNneY.6aD1Xs7w3MFG5oYr29FcmF/QHpSIG', '2025-08-22 10:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `fullname`, `age`, `gender`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'Tracy Odhiambo', 23, 'Female', 'tracy@gmail.com', '0756746543', '$2y$10$rUIYn4zjQPllu1rHBbhSV.AoLMKcoBvp2SekOP2p06I1XTIe.xnJi', '2025-08-19 10:49:36'),
(2, 'James Okumu', 33, 'Male', 'james@afyacheck.com', '0788675433', '$2y$10$7bC0RiBUBTLvVzjgCPBUduTqmErPE83dutNQrluHU9eZQtTEdAgKy', '2025-08-19 19:40:37'),
(3, 'John Githinji', 65, 'Male', 'johng@afyacheck.com', '078765453423', '$2y$10$Cj4u2boYdfOAaQOn6z.kyOOHFwvEmk.cpLUA6QBvTICWlZ5tYoV2W', '2025-08-22 07:52:40'),
(4, 'Gladys Wanga', 17, 'Female', 'gladys@afyacheck.com', '0745341232', '$2y$10$4hD/KIJJVKfIywccOwkBp.3ZzXMOxqgo7yRnRtaAW1eFB6KqNmN.W', '2025-08-22 14:37:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bp_readings`
--
ALTER TABLE `bp_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bp_readings`
--
ALTER TABLE `bp_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `bp_readings`
--
ALTER TABLE `bp_readings`
  ADD CONSTRAINT `bp_readings_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 16, 2024 at 08:07 PM
-- Server version: 8.0.39-0ubuntu0.24.04.2
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `home_automation`
--

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

CREATE TABLE `credentials` (
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `credentials`
--

INSERT INTO `credentials` (`username`, `password`) VALUES
('Admin', 'SmartRoom@slc');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int NOT NULL,
  `device_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `state` enum('ON','OFF') COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_name`, `state`, `last_update`) VALUES
(1, 'Fan1', 'ON', '2024-09-16 18:08:40'),
(2, 'Fan2', 'ON', '2024-09-16 17:53:02'),
(3, 'Fan3', 'ON', '2024-09-16 17:53:02'),
(4, 'Fan4', 'ON', '2024-09-16 17:53:02'),
(5, 'Light1', 'ON', '2024-09-16 17:53:12'),
(6, 'Light2', 'ON', '2024-09-16 19:05:47'),
(7, 'Light3', 'ON', '2024-09-16 19:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `esp_status`
--

CREATE TABLE `esp_status` (
  `status` enum('online','offline') COLLATE utf8mb4_general_ci NOT NULL,
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `device_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esp_status`
--

INSERT INTO `esp_status` (`status`, `last_update`, `device_name`) VALUES
('online', '2024-09-16 20:06:38', 'ESP8266');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esp_status`
--
ALTER TABLE `esp_status`
  ADD PRIMARY KEY (`status`),
  ADD UNIQUE KEY `device_name` (`device_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

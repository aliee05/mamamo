-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 03:06 PM
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
-- Database: `itc127-cs2a-2025-loayon`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `usertype` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) VALUES
('admin', '1234', 'ADMINISTRATOR', 'ACTIVE', 'admin_anna', '2025-04-12'),
('admin_anna', 'anna', 'ADMINISTRATOR', 'ACTIVE', 'admin', '2025-04-06'),
('alie', 'loayon', 'ADMINISTRATOR', 'ACTIVE', 'admin_anna', '2025-04-20'),
('anna', 'ANNA', 'ADMINISTRATOR', 'ACTIVE', 'admin_anna', '2025-04-16'),
('tech_anna05', 'anna', 'TECHNICAL', 'ACTIVE', 'admin', '2025-04-06'),
('user_anna22', 'anna', 'USER', 'ACTIVE', 'admin', '2025-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `tblequipments`
--

CREATE TABLE `tblequipments` (
  `assetnumber` varchar(50) NOT NULL,
  `serialnumber` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `yearmodel` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblequipments`
--

INSERT INTO `tblequipments` (`assetnumber`, `serialnumber`, `type`, `manufacturer`, `yearmodel`, `description`, `branch`, `department`, `status`, `createdby`, `datecreated`) VALUES
('5555', '53333', 'CPU', 'uhf', '2002', 'wertsydtu', 'Elisa Esguerra Campus (AU Malabon)', 'Marketing', 'Working', 'admin_anna', '2025-04-14'),
('asdf', 'asdfg', 'CPU', 'sdfcv', '2345', 'sdafsgdv', 'Plaridel Campus (AU Mandaluyong)', 'Academic track', 'Working', 'tech_anna05', '2025-04-16'),
('oooo', 'ooo1', 'Monitor', 'jsjsj', '2002', 'hehe', 'Arellano School of Law', 'Marketing', 'Working', 'admin_anna', '2025-04-07'),
('zdxfch', '433544', 'CPU', 'uhf', '2002', 'rseydtfutugyiul', 'Plaridel Campus (AU Mandaluyong)', 'Academic track', 'Working', 'admin_anna', '2025-04-14');

-- --------------------------------------------------------

--
-- Table structure for table `tbllogs`
--

CREATE TABLE `tbllogs` (
  `datelog` varchar(20) NOT NULL,
  `timelog` varchar(20) NOT NULL,
  `action` varchar(20) NOT NULL,
  `module` varchar(20) NOT NULL,
  `performedto` varchar(50) NOT NULL,
  `performedby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllogs`
--

INSERT INTO `tbllogs` (`datelog`, `timelog`, `action`, `module`, `performedto`, `performedby`) VALUES
('2025-04-20', '21:03:03', 'Add', 'Account Management', 'alie', 'admin_anna'),
('2025-04-20', '21:03:17', 'Assign', 'Ticket Management', '20250416120637', 'admin_anna'),
('2025-04-20', '21:03:28', 'Approve', 'Ticket Management', '20250407102343', 'admin_anna'),
('2025-04-20', '21:03:39', 'Delete', 'Ticket Management', '20250407102343', 'admin_anna'),
('2025-04-20', '21:04:03', 'Complete', 'Ticket Management', '20250416120637', 'tech_anna05');

-- --------------------------------------------------------

--
-- Table structure for table `tblticket`
--

CREATE TABLE `tblticket` (
  `ticketnumber` varchar(50) NOT NULL,
  `problem` varchar(50) NOT NULL,
  `details` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `time` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `assignedto` varchar(50) NOT NULL,
  `dateassigned` varchar(50) NOT NULL,
  `datecompleted` varchar(50) NOT NULL,
  `approvedby` varchar(50) NOT NULL,
  `dateapproved` varchar(50) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblticket`
--

INSERT INTO `tblticket` (`ticketnumber`, `problem`, `details`, `status`, `time`, `createdby`, `datecreated`, `assignedto`, `dateassigned`, `datecompleted`, `approvedby`, `dateapproved`, `last_updated`) VALUES
('20250331062745', 'Connection', 'Pogi ako', 'CLOSED', '06:27:55', 'Russel', '2025-03-31', 'Ria', '2025-03-31 06:30:37', '2025-03-31 06:40:29', 'admin', '2025-03-31 06:41:23', '2025-03-30 22:41:23'),
('20250331062800', 'Software', 'Gwapo ako', 'CLOSED', '06:28:08', 'Russel', '2025-03-31', 'tech', '2025-03-31 06:30:44', '2025-03-31 06:40:55', 'admin', '2025-03-31 06:41:26', '2025-03-30 22:41:26'),
('20250407012656', 'Connection', 'qreaytesrt', 'CLOSED', '01:26:59', 'user_anna22', '2025-04-07', 'tech_anna05', '2025-04-07 10:24:30', '2025-04-07 10:25:29', 'admin_anna', '2025-04-07 10:26:52', '2025-04-07 02:26:52'),
('20250407102321', 'Hardware', 'mamamo', 'CLOSED', '10:23:27', 'user_anna22', '2025-04-07', 'tech_anna05', '2025-04-07 10:24:40', '2025-04-07 10:27:26', 'admin_anna', '2025-04-07 10:27:55', '2025-04-07 02:27:55'),
('20250407102334', 'Software', 'asjndj', 'CLOSED', '10:23:40', 'user_anna22', '2025-04-07', 'tech_anna05', '2025-04-07 10:24:55', '2025-04-16 11:14:29', 'admin_anna', '2025-04-16 11:46:05', '2025-04-16 03:46:05'),
('20250416120637', 'Hardware', 'asdf', 'WAITING FOR APPROVAL', '12:06:41', 'user_anna22', '2025-04-16', 'tech_anna05', '2025-04-20 21:03:17', '2025-04-20 21:04:03', '', '', '2025-04-20 13:04:03'),
('20250416121226', 'Hardware', 'sample', 'PENDING', '12:12:33', 'user_anna22', '2025-04-16', '', '', '', '', '', '2025-04-16 04:12:33');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('ADMIN','TECHNICAL','USER') NOT NULL DEFAULT 'USER',
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `username`, `email`, `password`, `usertype`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin_anna', 'admin1@example.com', '$2y$10$4nz41Oxmyawa/CBcWlEpYeAevJZYfCVohlowY0dhxQvhmCA9OG7UW', 'ADMIN', 'ACTIVE', '2025-04-06 18:38:08', '2025-04-09 09:08:28', NULL),
(2, 'tech1', 'tech1@example.com', '1234', 'TECHNICAL', 'ACTIVE', '2025-04-06 18:38:08', '2025-04-06 18:39:10', NULL),
(3, 'user1', 'user1@example.com', '1234', 'USER', 'ACTIVE', '2025-04-06 18:38:08', '2025-04-06 18:39:15', NULL),
(4, 'user2', 'user2@example.com', '1234', 'USER', 'INACTIVE', '2025-04-06 18:38:08', '2025-04-06 18:39:19', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `tblequipments`
--
ALTER TABLE `tblequipments`
  ADD PRIMARY KEY (`assetnumber`),
  ADD UNIQUE KEY `serialnumber` (`serialnumber`);

--
-- Indexes for table `tblticket`
--
ALTER TABLE `tblticket`
  ADD PRIMARY KEY (`ticketnumber`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_usertype` (`usertype`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

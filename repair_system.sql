-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2025 at 06:14 PM
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
-- Database: `repair_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `dorms`
--

CREATE TABLE `dorms` (
  `DormID` int(11) NOT NULL,
  `DormName` varchar(255) NOT NULL COMMENT 'ชื่อหอ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `dorms`
--

INSERT INTO `dorms` (`DormID`, `DormName`) VALUES
(1, 'หอพักชาย'),
(2, 'หอพักหญิง1');

-- --------------------------------------------------------

--
-- Table structure for table `floors`
--

CREATE TABLE `floors` (
  `FloorID` int(11) NOT NULL,
  `FloorName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `floors`
--

INSERT INTO `floors` (`FloorID`, `FloorName`) VALUES
(1, 'ชั้น 1'),
(2, 'ชั้น 2');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `ReportID` int(11) NOT NULL COMMENT 'ID เก็บการรายงานต่างๆ',
  `NameReport` varchar(255) NOT NULL COMMENT 'หัวข้อชื่อรายงาน',
  `Description` text NOT NULL COMMENT 'รายะเอียดปัญหา',
  `FloorID` int(11) NOT NULL COMMENT 'หมายเลขชั้น',
  `UserID` int(11) NOT NULL COMMENT 'เก็บIDคนแจ้ง',
  `Picture` varchar(255) DEFAULT NULL COMMENT 'รูปภาพ',
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'แจ้งเมื่อเวลา',
  `DormID` int(11) NOT NULL COMMENT 'ID หอ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`ReportID`, `NameReport`, `Description`, `FloorID`, `UserID`, `Picture`, `Created_at`, `DormID`) VALUES
(1, 'น้ำไม่ไหล', 'น้ำไม่ไหล', 1, 1, NULL, '2025-03-08 15:48:14', 1),
(2, 'น้ำไม่ไหล1', 'น้ำไม่ไหล', 1, 1, NULL, '2025-03-08 15:50:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `report_completed`
--

CREATE TABLE `report_completed` (
  `ID` int(11) NOT NULL,
  `ReportID` int(11) DEFAULT NULL,
  `NameReport` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `report_completed`
--

INSERT INTO `report_completed` (`ID`, `ReportID`, `NameReport`, `Description`, `Created_at`) VALUES
(1, 3, 'น้ำไม่ไหล2', 'safsafasfsaf', '2025-03-08 15:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL COMMENT 'เก็บไอดีผู้ใช้งาน',
  `Name` varchar(255) NOT NULL COMMENT 'เก็บชื่อ',
  `Email` varchar(255) NOT NULL COMMENT 'เก็บEmailในการlogin',
  `Password` varchar(255) NOT NULL,
  `RoleID` int(11) NOT NULL DEFAULT 1 COMMENT 'RoleID บอกตำแหน่ง',
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Name`, `Email`, `Password`, `RoleID`, `Created_at`) VALUES
(1, 'Tawan', 'ch@gmail.com', '123', 1, '2025-03-08 08:30:52'),
(6, 'we', 'ch1@gmail.com', '123', 2, '2025-03-08 09:19:03'),
(7, 'tuu', 't@gmail.com', '123', 1, '2025-03-08 16:47:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dorms`
--
ALTER TABLE `dorms`
  ADD PRIMARY KEY (`DormID`);

--
-- Indexes for table `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`FloorID`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `DormName` (`DormID`),
  ADD KEY `UserName` (`UserID`),
  ADD KEY `FloorName` (`FloorID`);

--
-- Indexes for table `report_completed`
--
ALTER TABLE `report_completed`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dorms`
--
ALTER TABLE `dorms`
  MODIFY `DormID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `floors`
--
ALTER TABLE `floors`
  MODIFY `FloorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID เก็บการรายงานต่างๆ', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_completed`
--
ALTER TABLE `report_completed`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'เก็บไอดีผู้ใช้งาน', AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `DormName` FOREIGN KEY (`DormID`) REFERENCES `dorms` (`DormID`),
  ADD CONSTRAINT `FloorName` FOREIGN KEY (`FloorID`) REFERENCES `floors` (`FloorID`),
  ADD CONSTRAINT `UserName` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

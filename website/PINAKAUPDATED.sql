-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 09:22 PM
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
-- Database: `pupwesto`
--

-- --------------------------------------------------------

--
-- Table structure for table `particular`
--

CREATE TABLE `particular` (
  `particulars_code` char(5) NOT NULL,
  `particulars` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `particular`
--

INSERT INTO `particular` (`particulars_code`, `particulars`) VALUES
('ET4', 'Extension'),
('LT1', 'LED TV'),
('PJ3', 'Projector'),
('VH2', 'VGA/HDMI');

-- --------------------------------------------------------

--
-- Table structure for table `quantity`
--

CREATE TABLE `quantity` (
  `reservation_id` char(10) NOT NULL,
  `particulars_code` char(5) NOT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quantity`
--

INSERT INTO `quantity` (`reservation_id`, `particulars_code`, `quantity`) VALUES
('R002', 'LT1', 3),
('R002', 'PJ3', 1),
('R003', 'ET4', 3),
('R004', 'ET4', 5),
('R004', 'LT1', 1),
('R004', 'PJ3', 1),
('R005', 'ET4', 5),
('R006', 'LT1', 2),
('R007', 'PJ3', 1),
('R008', 'LT1', 1),
('R009', 'ET4', 2),
('R010', 'VH2', 3),
('R011', 'PJ3', 1),
('R012', 'ET4', 2),
('R013', 'LT1', 1),
('R017', 'LT1', 1),
('R018', 'ET4', 2),
('R019', 'PJ3', 2);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` char(10) NOT NULL,
  `user_id` char(15) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_number` varchar(4) NOT NULL,
  `expected_num_people` int(11) DEFAULT NULL,
  `professor_in_charge` varchar(50) DEFAULT NULL,
  `purpose_of_reservation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `user_id`, `date`, `start_time`, `end_time`, `room_number`, `expected_num_people`, `professor_in_charge`, `purpose_of_reservation`) VALUES
('R002', '2023-00452-MN-0', '2025-03-29', '07:00:00', '09:30:00', 'W402', 58, 'Lydinar Dastas ', 'Class Lecture'),
('R003', '2023-00452-MN-0', '2025-03-19', '10:00:00', '13:00:00', 'N503', 43, 'Monina Baretto', 'Meeting'),
('R004', '2023-04956-MN-0', '2025-04-21', '16:30:00', '19:00:00', 'W511', 55, 'Leo Violeta', 'Workshop'),
('R005', '2023-04956-MN-0', '2025-04-30', '14:00:00', '17:00:00', 'N509', 100, 'Dustin Santos', 'Event'),
('R006', '2020-00001-MN-0', '2025-05-08', '13:00:00', '16:00:00', 'S502', 23, NULL, 'Meeting'),
('R007', '2023-00368-MN-0', '2025-05-10', '09:00:00', '11:00:00', 'S502', 30, 'Dr. Jane Doe', 'Workshop'),
('R008', '2023-03312-MN-0', '2025-04-18', '14:00:00', '17:00:00', 'S507', 55, 'Monina Baretto', 'Event'),
('R009', '2023-04186-MN-0', '2025-05-01', '10:00:00', '12:00:00', 'W508', 25, 'Leo Violeta', 'Workshop'),
('R010', '2025-00124-MN-0', '2025-05-05', '08:00:00', '10:00:00', 'N101', 40, 'Prof. Amanda Cruz', 'Class Lecture'),
('R011', '2025-00125-MN-0', '2025-05-05', '10:30:00', '12:30:00', 'N101', 35, 'Prof. John Velasco', 'Meeting'),
('R012', '2025-00126-MN-0', '2025-05-06', '09:00:00', '11:00:00', 'W201', 50, 'Prof. Erika Santos', 'Workshop'),
('R013', '2025-00127-MN-0', '2025-05-06', '13:00:00', '15:00:00', 'W201', 30, 'Prof. Gary Dizon', 'Class Lecture'),
('R014', '2025-00128-MN-0', '2025-05-07', '14:00:00', '16:00:00', 'S601', 45, 'Prof. Nica Robles', 'Event'),
('R015', '2025-00129-MN-0', '2025-05-08', '08:00:00', '10:00:00', 'N301', 60, NULL, 'Class Lecture'),
('R016', '2025-00130-MN-0', '2025-05-08', '10:30:00', '12:30:00', 'N301', 55, NULL, 'Workshop'),
('R017', '2025-00131-MN-0', '2025-05-10', '08:00:00', '10:00:00', 'S505', 25, 'Prof. Joel Diaz', 'Class Lecture'),
('R018', '2025-00132-MN-0', '2025-05-11', '10:30:00', '12:00:00', 'W306', 30, 'Prof. Annie Santos', 'Workshop'),
('R019', '2025-00133-MN-0', '2025-05-12', '13:00:00', '15:00:00', 'E404', 20, 'Prof. Ron Dela Cruz', 'Meeting');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `room_number` varchar(10) NOT NULL,
  `building` varchar(20) DEFAULT NULL,
  `room_type` varchar(20) DEFAULT NULL,
  `status` varchar(15) DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`room_number`, `building`, `room_type`, `status`) VALUES
('E300a', 'East', 'Classroom', 'Available'),
('E301', 'East', 'Classroom', 'Available'),
('E302', 'East', 'Classroom', 'Available'),
('E303', 'East', 'Classroom', 'Available'),
('E304', 'East', 'Classroom', 'Available'),
('E305', 'East', 'Classroom', 'Available'),
('E306', 'East', 'Classroom', 'Available'),
('E307', 'East', 'Classroom', 'Available'),
('E308', 'East', 'Classroom', 'Available'),
('E309', 'East', 'Classroom', 'Available'),
('E310', 'East', 'Classroom', 'Available'),
('E311', 'East', 'Classroom', 'Available'),
('E312', 'East', 'Classroom', 'Available'),
('E313', 'East', 'Classroom', 'Available'),
('E401', 'East', 'Classroom', 'Available'),
('E403', 'East', 'Classroom', 'Available'),
('E405', 'East', 'Classroom', 'Available'),
('E407', 'East', 'Classroom', 'Available'),
('E409', 'East', 'Classroom', 'Available'),
('E411', 'East', 'Classroom', 'Available'),
('E413', 'East', 'Classroom', 'Available'),
('E415', 'East', 'Classroom', 'Available'),
('E417', 'East', 'Classroom', 'Available'),
('E501', 'East', 'Classroom', 'Available'),
('E502', 'East', 'Classroom', 'Available'),
('E503', 'East', 'Classroom', 'Available'),
('E504', 'East', 'Classroom', 'Available'),
('E505', 'East', 'Classroom', 'Available'),
('E506', 'East', 'Classroom', 'Available'),
('E507', 'East', 'Classroom', 'Available'),
('E508', 'East', 'Classroom', 'Available'),
('E509', 'East', 'Classroom', 'Available'),
('E510', 'East', 'Classroom', 'Available'),
('E511', 'East', 'Classroom', 'Available'),
('E512', 'East', 'Classroom', 'Available'),
('E513', 'East', 'Classroom', 'Available'),
('E514', 'East', 'Classroom', 'Available'),
('E515', 'East', 'Classroom', 'Available'),
('E516', 'East', 'Classroom', 'Available'),
('E517', 'East', 'Classroom', 'Available'),
('E518', 'East', 'Classroom', 'Available'),
('E601', 'East', 'Classroom', 'Available'),
('N300', 'North', 'Classroom', 'Available'),
('N301', 'North', 'Classroom', 'Available'),
('N302', 'North', 'Classroom', 'Available'),
('N303', 'North', 'Classroom', 'Available'),
('N304', 'North', 'Classroom', 'Available'),
('N305', 'North', 'Classroom', 'Available'),
('N306', 'North', 'Classroom', 'Available'),
('N307', 'North', 'Classroom', 'Available'),
('N308', 'North', 'Classroom', 'Available'),
('N309', 'North', 'Classroom', 'Available'),
('N310', 'North', 'Classroom', 'Available'),
('N311', 'North', 'Classroom', 'Available'),
('N312', 'North', 'Classroom', 'Available'),
('N313', 'North', 'Classroom', 'Available'),
('N314', 'North', 'Classroom', 'Available'),
('N315', 'North', 'Classroom', 'Available'),
('N316', 'North', 'Classroom', 'Available'),
('N317', 'North', 'Classroom', 'Available'),
('N318', 'North', 'Classroom', 'Available'),
('N400', 'North', 'Classroom', 'Available'),
('N401', 'North', 'Classroom', 'Available'),
('N402', 'North', 'Classroom', 'Available'),
('N403', 'North', 'Classroom', 'Available'),
('N404', 'North', 'Classroom', 'Available'),
('N405', 'North', 'Classroom', 'Available'),
('N406', 'North', 'Classroom', 'Available'),
('N407', 'North', 'Classroom', 'Available'),
('N408', 'North', 'Classroom', 'Available'),
('N409', 'North', 'Classroom', 'Available'),
('N410', 'North', 'Classroom', 'Available'),
('N411', 'North', 'Classroom', 'Available'),
('N412', 'North', 'Classroom', 'Available'),
('N413', 'North', 'Classroom', 'Available'),
('N414', 'North', 'Classroom', 'Available'),
('N415', 'North', 'Classroom', 'Available'),
('N416', 'North', 'Classroom', 'Available'),
('N417', 'North', 'Classroom', 'Available'),
('N418', 'North', 'Classroom', 'Available'),
('S501', 'South', 'Laboratory', 'Available'),
('S502', 'South', 'Laboratory', 'Available'),
('S503', 'South', 'Laboratory', 'Available'),
('S503A', 'South', 'Laboratory', 'Available'),
('S503B', 'South', 'Laboratory', 'Available'),
('S504', 'South', 'Laboratory', 'Available'),
('S505', 'South', 'Laboratory', 'Available'),
('S508', 'South', 'Laboratory', 'Available'),
('S509', 'South', 'Laboratory', 'Available'),
('S510', 'South', 'Laboratory', 'Available'),
('S511', 'South', 'Laboratory', 'Available'),
('S512B', 'South', 'Laboratory', 'Available'),
('S513', 'South', 'Laboratory', 'Available'),
('S515', 'South', 'Laboratory', 'Available'),
('S517', 'South', 'Laboratory', 'Available'),
('S518', 'South', 'Laboratory', 'Available'),
('W301', 'West', 'Classroom', 'Available'),
('W303', 'West', 'Classroom', 'Available'),
('W304', 'West', 'Classroom', 'Available'),
('W305', 'West', 'Classroom', 'Available'),
('W306', 'West', 'Classroom', 'Available'),
('W307', 'West', 'Classroom', 'Available'),
('W308', 'West', 'Classroom', 'Available'),
('W309', 'West', 'Classroom', 'Available'),
('W310', 'West', 'Classroom', 'Available'),
('W311', 'West', 'Classroom', 'Available'),
('W312', 'West', 'Classroom', 'Available'),
('W314', 'West', 'Classroom', 'Available'),
('W316', 'West', 'Classroom', 'Available'),
('W318', 'West', 'Classroom', 'Available'),
('W401', 'West', 'Classroom', 'Available'),
('W403', 'West', 'Classroom', 'Available'),
('W404', 'West', 'Classroom', 'Available'),
('W405', 'West', 'Classroom', 'Available'),
('W406', 'West', 'Classroom', 'Available'),
('W407', 'West', 'Classroom', 'Available'),
('W408', 'West', 'Classroom', 'Available'),
('W409', 'West', 'Classroom', 'Available'),
('W410', 'West', 'Classroom', 'Available'),
('W411', 'West', 'Classroom', 'Available'),
('W412', 'West', 'Classroom', 'Available'),
('W413', 'West', 'Classroom', 'Available'),
('W414', 'West', 'Classroom', 'Available'),
('W415', 'West', 'Classroom', 'Available'),
('W416', 'West', 'Classroom', 'Available'),
('W417', 'West', 'Classroom', 'Available'),
('W420', 'West', 'Classroom', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` char(15) NOT NULL,
  `password` varchar(25) NOT NULL,
  `user_role` varchar(10) DEFAULT NULL,
  `full_name` varchar(50) NOT NULL,
  `contact_num` varchar(15) NOT NULL,
  `email` varchar(50) NOT NULL,
  `program` char(10) DEFAULT NULL,
  `block` int(11) DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  `department` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `password`, `user_role`, `full_name`, `contact_num`, `email`, `program`, `block`, `section`, `department`) VALUES
('2020-00001-MN-0', '', 'Teacher', 'Kors Michael', '09245175829', 'michaelkors21@gmail.com', NULL, NULL, NULL, 'CCIS'),
('2023-00368-MN-0', '', 'Student', 'Aldover Joanne', '09181234567', 'joannealdover@gmail.com', 'BSCS', 1, 4, 'CCIS'),
('2023-00452-MN-0', '', 'Student', 'Lucas Charles Czar G.', '09234567890', 'charleslucas@gmail.com', 'BSCS', 1, 2, 'CCIS'),
('2023-02760-MN-0', 'nicolas100200', 'Student', 'John Rich Nicolas', '09165638113', 'johnrichnicolas@gmail.com', 'BSCS', 2, 1, 'CCIS'),
('2023-03312-MN-0', '', 'Student', 'Tamares John Paul', '09171234567', 'jptamares@gmail.com', 'BSCS', 2, 5, 'CCIS'),
('2023-04186-MN-0', '', 'Student', 'Lasco Ed Marcel G', '92929292928', 'edmarcellasco@gmail.com', 'BSCS', 2, 1, 'CCIS'),
('2023-04956-MN-0', '', 'Student', 'Lizarondo Sophia I.', '09158887014', 'sophializarondo25@gmail.com', 'BSCS', 2, 1, 'BSCS'),
('2025-00124-MN-0', '', 'Student', 'Reyes Daniel S.', '09193456789', 'danielreyes04@gmail.com', 'BSCS', 3, 2, 'CCIS'),
('2025-00125-MN-0', '', 'Student', 'Garcia Mei L.', '09281234567', 'meilgarcia12@gmail.com', 'BSCS', 2, 1, 'CCIS'),
('2025-00126-MN-0', '', 'Student', 'Fernandez Kyle R.', '09172345678', 'kylefernandez98@gmail.com', 'BSCS', 1, 3, 'CCIS'),
('2025-00127-MN-0', '', 'Student', 'Santos Beatrice V.', '09081239876', 'beatricesantos@gmail.com', 'BSCS', 3, 1, 'CCIS'),
('2025-00128-MN-0', '', 'Student', 'Torres Miguel J.', '09361234567', 'migueltorres22@gmail.com', 'BSCS', 2, 2, 'CCIS'),
('2025-00129-MN-0', '', 'Teacher', 'Ramirez Helena F.', '09184567890', 'helenaramirez@ccis.edu.ph', NULL, NULL, NULL, 'CCIS'),
('2025-00130-MN-0', '', 'Teacher', 'Domingo Carlo V.', '09275678901', 'carlodomingo@ccis.edu.ph', NULL, NULL, NULL, 'CCIS'),
('2025-00131-MN-0', '', 'Student', 'Rivera Mark L.', '09178889999', 'markrivera@ccis.edu.ph', 'BSIT', 1, 1, 'CCIS'),
('2025-00132-MN-0', '', 'Student', 'Delos Santos Anne M.', '09175556666', 'annedelos@ccis.edu.ph', 'BSIT', 2, 2, 'CCIS'),
('2025-00133-MN-0', '', 'Student', 'Cruz John Michael', '09223334444', 'jmcruz@ccis.edu.ph', 'BSIT', 3, 1, 'CCIS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `particular`
--
ALTER TABLE `particular`
  ADD PRIMARY KEY (`particulars_code`);

--
-- Indexes for table `quantity`
--
ALTER TABLE `quantity`
  ADD PRIMARY KEY (`reservation_id`,`particulars_code`),
  ADD KEY `reservation_id_idx` (`reservation_id`),
  ADD KEY `particulars_code_idx` (`particulars_code`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`room_number`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quantity`
--
ALTER TABLE `quantity`
  ADD CONSTRAINT `particulars_code` FOREIGN KEY (`particulars_code`) REFERENCES `particular` (`particulars_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservation_id` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

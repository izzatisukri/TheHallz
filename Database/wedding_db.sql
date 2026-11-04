-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 30, 2026 at 10:10 PM
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
-- Database: `wedding_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `admin_role`) VALUES
(1, 'Ahmad Zul', 'admin@gmail.com', '$2y$10$UoKP4xyUWAs.syPRvV4Hm.2JS9j8lD0JUMouwFtIh1KtQ0g5tMyGC', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `event_date` date NOT NULL,
  `num_of_guests` int(11) NOT NULL,
  `bookingstatus` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `client_id`, `venue_id`, `package_id`, `booking_date`, `event_date`, `num_of_guests`, `bookingstatus`) VALUES
(1, 1, 2, 1, '2026-06-29', '2026-11-19', 700, 'Confirmed'),
(2, 8, 2, 1, '2026-06-30', '2026-10-01', 1000, 'Confirmed'),
(3, 8, 8, 4, '2026-06-30', '2026-10-15', 700, 'Confirmed'),
(4, 7, 8, 4, '2026-06-30', '2026-10-15', 700, 'Pending'),
(5, 10, 2, 1, '2026-06-30', '2026-10-15', 500, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phonenum` varchar(20) NOT NULL,
  `client_password` varchar(255) NOT NULL,
  `client_role` varchar(20) NOT NULL,
  `client_status` varchar(50) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `client_name`, `client_email`, `client_phonenum`, `client_password`, `client_role`, `client_status`) VALUES
(1, 'Nurul \'Izzati', 'izzatishhh@gmail.com', '01157731190', '$2y$10$1Ew4ZdpkhkTSHjjb.XRGne424Jco9gRHO9/o.2Iiq.GTwGDvFG9i6', 'client', 'Active'),
(4, 'Mat Salleh', 'salleh@gmail.com', '0112345678', '$2y$10$igtWc3LtSGFYfmDeg3zh7efg7BgVnFKZioEx9T2WRoZl7DVC.3/bi', 'client', 'Active'),
(7, 'Ameena Sofia', 'ameena@gmail.com', '0186558324', '$2y$10$b/Sco1SGwlqX7wlknycMGOQR8Z6y3VkkM9ZnEUuh0I4EjpslTlxTq', 'client', 'Active'),
(8, 'Hani Dahlea', 'hani@gmail.com', '0163060370', '$2y$10$PQNa37BC0kKJPMWTHAcfcu1gAjuZQ78Db5weMnQtP1XQes4HftAzq', 'client', 'Active'),
(9, 'Batrisyia', 'batrisyia@gmail.com', '0129660952', '$2y$10$cnXV92HDRShCZs3.EngD4.cnJYjQT1yQzmLsIe3H.wVLhV151B/4S', 'client', 'Active'),
(10, 'Mariah Abu Bakar', 'mariah@gmail.com', '01156550722', '$2y$10$jU5r8f3mrYH0gbtZRyJg.OuaCfCficy0Y0V7M/6Sc5mia3qhKz0U6', 'client', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `package_price` decimal(10,2) NOT NULL,
  `package_inclusions` varchar(700) NOT NULL,
  `venue_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package`
--

INSERT INTO `package` (`package_id`, `package_name`, `package_price`, `package_inclusions`, `venue_id`) VALUES
(1, 'Basic Package', 12000.00, 'Standard pelamin setup\r\nStandard backdrop setup\r\nSimple floral decoration\r\n1 nasi (Nasi putih)\r\n2 lauk (Ayam masak merah + Daging masak hitam)\r\n1 sayur (Campur)\r\n1 buah (Tembikai)\r\n1 air (Sirap)', 2),
(4, 'Premium Package', 25000.00, 'Pelamin setup with floral design\r\nUpgraded backdrop design (Fabric drapping)\r\nMedium floral decoration\r\n3 nasi (Nasi putih)\r\n2 lauk (Ayam masak merah + Daging masak hitam)\r\n1 sayur (Campur)\r\n2 buah (Tembikai + Oren)\r\n2 air (Sirap + Oren)\r\n1 dessert (Kek Brownies)', 8),
(5, 'Premium Package', 25000.00, '-Nasi Putih\r\n-Nasi Minyak\r\n-Ayam Masak Merah\r\n-Daging Masak Hitam\r\n-Kambing Kuzi\r\n-Acar timun\r\n-Ulam-ulaman\r\n-Kuih Nona Manis\r\n-Butter Cake\r\n-Air Sunquick\r\n-Air Anggur', 9),
(6, 'Silver Package', 15000.00, 'Nasi putih\r\nNasi tomato\r\nAyam masak merah\r\nAyam berempah\r\nDaging kari\r\nApam Balik', 10);

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(100) NOT NULL,
  `venue_location` varchar(255) NOT NULL,
  `venue_capacity` int(11) NOT NULL,
  `venue_price` decimal(10,2) NOT NULL,
  `venue_desc` text NOT NULL,
  `owner_id` int(11) NOT NULL,
  `venue_ssm` varchar(20) NOT NULL,
  `venue_ssm_file` varchar(255) NOT NULL,
  `venue_image` text NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `venue_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`venue_id`, `venue_name`, `venue_location`, `venue_capacity`, `venue_price`, `venue_desc`, `owner_id`, `venue_ssm`, `venue_ssm_file`, `venue_image`, `admin_id`, `venue_status`) VALUES
(2, 'Orkid House', 'No 12, Jalan Melaka Raya 5, 75000 Melaka, Malaysia', 1000, 0.00, '', 1, '202301234567', '1782751123_ssmCert.jpg', '1782751123_weddingVenue1.jpg', NULL, 'Approved'),
(8, 'Dewan Serbaguna Semarak', 'No. 25, Jalan S2 B12, Green Street Homes, Seremban 2', 700, 0.00, '', 6, '202601012345', '1782805945_SSM Aufa.jpg', '1782805945_gambar hall aufa.jpg', NULL, 'Approved'),
(9, 'Rose Glass Hall', 'Lot PT 7658, DT Jalan Autocity 2, Persiaran Autocity, Jalan Hang Tuah Jaya, 76100 Durian Tunggal', 1000, 0.00, '', 6, '202601012345', '1782840796_SSM Aufa.jpg', '1782840796_Rose Glass Hall.jpg', NULL, 'Approved'),
(10, 'Daza Villa Wedding Hall', 'Lot 9258, Jalan Kampung Perepat, 42200 Klang, Selangor, Malaysia.', 800, 0.00, '', 9, '202312345678', '1782843664_SSM.jpg', '1782843664_daud wedding hall.jpg', NULL, 'Approved'),
(11, 'Ivory Pearl Ballroom', 'No. 55, Jalan Cempaka 8, Taman Indah, 70100 Seremban, Negeri Sembilan', 1500, 0.00, '', 8, '202401123456', '1782844239_SSM.jpg', '1782844239_wedding hall ammar.jpg', NULL, 'Pending'),
(12, 'Emerald Crown Hall', 'No. 9, Jalan Sutera 3, Bandar Impian, 40150 Shah Alam, Selangor', 500, 0.00, '', 9, '202208998877', '1782844569_SSM.jpg', '1782844569_pelamin1.jpg', NULL, 'Pending'),
(13, 'Crystal Blossom Ballroom', 'No. 88, Persiaran Mutiara 2, Bandar Harmoni, 81100 Johor Bahru, Johor', 1000, 0.00, '', 7, '202205456789', '1782844977_SSM.jpg', '1782844977_wedding abbas.jpg', NULL, 'Pending'),
(14, 'The Ever After Hall', 'No. 35, Jalan Seri Putra 4, Bandar Seri Putra, 43000 Bangi, Selangor', 700, 0.00, '', 10, '202304789012', '1782846697_SSM Aufa.jpg', '1782846972_weddinh hall syed.jpg', NULL, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `venue_owner`
--

CREATE TABLE `venue_owner` (
  `owner_id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_email` varchar(100) NOT NULL,
  `owner_phonenum` varchar(20) NOT NULL,
  `owner_password` varchar(255) NOT NULL,
  `owner_role` varchar(20) NOT NULL,
  `owner_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_owner`
--

INSERT INTO `venue_owner` (`owner_id`, `owner_name`, `owner_email`, `owner_phonenum`, `owner_password`, `owner_role`, `owner_status`) VALUES
(1, 'Alliyana Shafiqah', 'alliyana@gmail.com', '0123456789', '$2y$10$EEzTkesAP6cHiAnpcBkIP.AdoEvYa1zdXaTnQmccPospGFyz.tTty', 'venue_owner', 'Active'),
(2, 'Nyra Ameena', 'nyra@gmail.com', '0174456378', 'Nyra@1234', 'venue_owner', 'Active'),
(3, 'Alayra Dzakira', 'alayra@gamil.com', '0139456321', 'Alayra@1234', 'venue_owner', 'Active'),
(4, 'Haydar Omar', 'haydar@1234', '0194786298', 'Haydar@1234', 'venue_owner', 'Active'),
(5, 'Raees Imran', 'raees@gmail.com', '0175984932', 'Raees@1234', 'venue_owner', 'Active'),
(6, 'Aufa Aufiya', 'aufa@gmail.com', '0187710255', '$2y$10$.0HZNnI.7owlg82ljSa8OeIZ/B3NBIZPR2WlZ6FSNyRaHr3xqS0qq', 'venue_owner', 'Active'),
(7, 'Abbas Abqari', 'abbas@gmail.com', '0192004589', '$2y$10$a26nG5trERd92RycPHfRd.NrGQ4tn56OkSHgmbW9Qw64A/hmTe6DK', 'venue_owner', 'Active'),
(8, 'Ammar Zahrain', 'ammar@gmail.com', '01945781180', '$2y$10$/Q.tIYKBmyetYOny2zTnE.VzcyxmA6YC5E91XASaEsnLdWvy8De3K', 'venue_owner', 'Active'),
(9, 'Daud ', 'daud@gmail.com', '01756558907', '$2y$10$5jpUnF.CyfDaLNssLqY9j.sVZWMWNkuIopXdHSN39bLhqLObgdhY.', 'venue_owner', 'Active'),
(10, 'Syed Muhd', 'syed@gmail.com', '01157789907', '$2y$10$VRvSm6FOT77pomo2w9cx2.uQ/oqdR12mmzqebq7fl14GByjP/1bf2', 'venue_owner', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `client_email` (`client_email`);

--
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `package_ibfk_1` (`venue_id`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`venue_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `venue_ibfk_2` (`admin_id`);

--
-- Indexes for table `venue_owner`
--
ALTER TABLE `venue_owner`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `owner_email` (`owner_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `venue_owner`
--
ALTER TABLE `venue_owner`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `package` (`package_id`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`venue_id`);

--
-- Constraints for table `package`
--
ALTER TABLE `package`
  ADD CONSTRAINT `package_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`venue_id`);

--
-- Constraints for table `venue`
--
ALTER TABLE `venue`
  ADD CONSTRAINT `venue_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `venue_owner` (`owner_id`),
  ADD CONSTRAINT `venue_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

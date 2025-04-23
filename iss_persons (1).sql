-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 06:57 PM
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
-- Database: `cis355`
--

-- --------------------------------------------------------

--
-- Table structure for table `iss_persons`
--

CREATE TABLE `iss_persons` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pwd_hash` varchar(255) NOT NULL,
  `pwd_salt` varchar(255) NOT NULL,
  `admin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_persons`
--

INSERT INTO `iss_persons` (`id`, `fname`, `lname`, `mobile`, `email`, `pwd_hash`, `pwd_salt`, `admin`) VALUES
(1, 'George', 'Corser', '', 'gp@c', '1b00138b08c3876a491f82a70f70de42', 'JKL25', ''),
(2, 'Amity', 'Blight', '', 'WitchChick128@gmail.com', '7920809819e5b322abb185f7f70c2509', '4FJk4S67', 'Y'),
(3, 'Luz', 'Noceda', '', 'Luzura@gmail.com', 'c4d3bfb826465d35acfef826918016b4', '94678cb055316e87', '0'),
(4, 'Willow', 'Park', '', 'HELLOW_WILLOW@gmail.com', 'ec1c66e459a1a667e797738d8ca958eb', '7d82ad321d6353f4', '1'),
(6, 'Amelia', 'Blight', '269', 'Number1BigSis@gmail.com', 'a830e284850d2635c66a99a8eb543449', '2ee6ab666ebb82e4', 'Y'),
(9, 'Hunter', 'Noceda', '', 'CosmicFrontier@f4n', '9ea093a2f8abdc2b04b4919e7d1719ce', 'e4248828b2bf703f', '0'),
(10, 'Salix', 'Parks', '', 'Smartest@alwaysright.com', 'e6644bdf0fb212479a7404a5238fa460', '3c8420937c02dd3a', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iss_persons`
--
ALTER TABLE `iss_persons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iss_persons`
--
ALTER TABLE `iss_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

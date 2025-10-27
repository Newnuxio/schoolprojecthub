-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 19, 2025 at 09:59 PM
-- Server version: 5.7.24
-- PHP Version: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eindproduct`
--

-- --------------------------------------------------------

--
-- Table structure for table `klanten`
--

CREATE TABLE `klanten` (
  `id` int(11) NOT NULL,
  `naam` text NOT NULL,
  `email` text NOT NULL,
  `wachtwoord` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `klanten`
--

INSERT INTO `klanten` (`id`, `naam`, `email`, `wachtwoord`) VALUES
(1, 'Nick de Rooij', 'nickrooij28@gmail.com', '$2y$10$vac01UEwgC2BieMGlIh1HOjeqkY/9yaJXiX6j.hf.eoQsEsOYd1Na'),
(4, 'dominic', 'dominic@gmail.com', '$2y$10$Tcyr6Mea7nmKH3nYZsZbbe5ixtOYqXQsX4nZcDaGdORz4A9MhPFUG'),
(5, 'a', 'a@a.nl', '$2y$10$/ZyHnpPrC3Q8y9.uSezyy.S5QdYuAT3IjoAolw9yBd5gPxlXCovEe');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klanten`
--
ALTER TABLE `klanten`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `klanten`
--
ALTER TABLE `klanten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

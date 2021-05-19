-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2020 at 09:30 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rest`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(120) NOT NULL,
  `last_name` varchar(120) NOT NULL,
  `nickname` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `nickname`, `email`, `age`, `password`, `hash`) VALUES
(1, 'Ivan', 'Dubrovskiy', 'DuB', 'dubrovskiy.i@mail.com', 28, 'qwerty', '$2y$10$cXUZKLAFN./X6xAlb3yKJ.lP5aSObdMZOGFEH.WHVRU0KvZUZQtG2'),
(2, 'Oleg', 'Petrovich', 'OlegPet', 'opetrov@mail.com', 43, 'qwerty123', '$2y$10$9S17KfV.beL8oauiIMpFQ.erfvxWq.RlC9YXDaahI1Jht/vCRq29W'),
(3, 'Vasya', 'Jiguly', 'Jigga', 'jigvasya@mail.com', 24, 'qwerty123!!!', NULL),
(4, 'Vito', 'Ferarri', 'vito', 'vferr@mail.com', 39, 'vito1111', '$2y$10$R1APzXfupCFGfclZgJJ5RuO/w1hzN14K0PUuvYku7lEHQVp/S4rb.'),
(26, 'laravel', '8', 'lara', 'lara@fabian.com', 0, '43234', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nickname` (`nickname`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

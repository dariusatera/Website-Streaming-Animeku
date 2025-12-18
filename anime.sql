-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2025 at 08:10 PM
-- Server version: 11.4.9-MariaDB-cll-lve
-- PHP Version: 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anij9233_animeku`
--

-- --------------------------------------------------------

--
-- Table structure for table `anime`
--

CREATE TABLE `anime` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `japan_title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `genres` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_episodes` int(11) DEFAULT NULL,
  `video_source` varchar(255) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `current_ep` varchar(50) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `type` varchar(50) DEFAULT NULL,
  `studio` varchar(100) DEFAULT NULL,
  `date_aired` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `score` varchar(50) DEFAULT NULL,
  `rating` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anime`
--

INSERT INTO `anime` (`id`, `title`, `japan_title`, `image`, `genres`, `description`, `total_episodes`, `video_source`, `tags`, `current_ep`, `view_count`, `type`, `studio`, `date_aired`, `status`, `score`, `rating`, `duration`) VALUES
(1, 'Kaiju No.8', '怪獣8号 (Kaijuu Hachigou)', 'img/trending/Kaiju_No8.jpg', 'Action, Sci-Fi, Shounen', 'Bercerita tentang Kafka Hibino yang ingin bergabung dengan Defense Force untuk melawan monster raksasa (Kaiju). Ini adalah kisah perjuangan dan persahabatan di tengah ancaman Kaiju.', 12, 'videos/1/ep-01.mp4', 'Active, BD', '12 / 12', 9141, 'TV Series', 'Production I.G', 'Apr 13, 2024 to ?', 'Airing', '8.15 / 10K', '9.0 / 200 times', '23 min/ep'),
(2, 'Kaoru Hana wa Rin to Saku', '薫る花は凛と咲く', 'img/trending/Waguri.webp', 'Slice of Life, Romance, School', 'Rintarou Kaoruko, seorang siswa SMA yang berjuang dengan masalah pribadinya, bertemu dengan Kaoru Hanako, seorang gadis yang hidupnya tampak sempurna. Kisah romantis yang mengharukan.', 12, 'videos/2/ep-01.mp4', 'Completed, Manga', '12 / 12', 4000, 'Manga Adapt.', 'Lerche', 'Aug 13, 2022 to ?', 'Completed', '8.60 / 5K', '9.2 / 100 times', '24 min/ep');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anime`
--
ALTER TABLE `anime`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anime`
--
ALTER TABLE `anime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

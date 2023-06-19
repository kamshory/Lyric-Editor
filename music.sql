-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2023 at 08:12 AM
-- Server version: 10.11.0-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `music`
--

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

CREATE TABLE `album` (
  `album_id` varchar(50) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

CREATE TABLE `artist` (
  `artist_id` varchar(40) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `sex` varchar(2) DEFAULT NULL,
  `birth_day` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `artist`
--

INSERT INTO `artist` (`artist_id`, `name`, `sex`, `birth_day`) VALUES
('11111', 'Dika', NULL, NULL),
('11112', 'Dini', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

CREATE TABLE `song` (
  `song_id` varchar(50) NOT NULL,
  `title` text DEFAULT NULL,
  `album_id` varchar(50) DEFAULT NULL,
  `artist_id` varchar(50) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_md5` varchar(32) DEFAULT NULL,
  `duration` float DEFAULT NULL,
  `genre_id` varchar(50) DEFAULT NULL,
  `lyric` longtext DEFAULT NULL,
  `time_create` timestamp NULL DEFAULT NULL,
  `time_edit` timestamp NULL DEFAULT NULL,
  `ip_create` varchar(50) DEFAULT NULL,
  `ip_edit` varchar(50) DEFAULT NULL,
  `admin_create` varchar(50) DEFAULT NULL,
  `admin_edit` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `song`
--

INSERT INTO `song` (`song_id`, `title`, `album_id`, `artist_id`, `path`, `file_size`, `file_md5`, `duration`, `genre_id`, `lyric`, `time_create`, `time_edit`, `ip_create`, `ip_edit`, `admin_create`, `admin_edit`, `active`) VALUES
('0648baa41ec2e662c2ce', 'Bahagia Bersamamu', '123', 'aaaaaaaa', NULL, NULL, NULL, NULL, NULL, '00:00:01,840 --> 00:00:09,080\nPagi yang cerah Matahari bersinar\n\n00:00:11,640 --> 00:00:17,500\nBunga bunga bersemi Burung burung pun bernyanyi\n\n00:00:19,520 --> 00:00:26,991\nJalanan ini Kutempuh tiap hari\n\n00:00:32,800 --> 00:00:50,891\nUntuk meraih mimpi Yang masih jadi misteri\n\n00:01:03,200 --> 00:01:21,291\nTak kan pernah kukeluhkan semua\n\n00:01:30,400 --> 00:01:48,491\nSegala usaha pasti akan aku coba\n\n00:01:57,120 --> 00:02:14,540\nWalau harus kutinggalkan rumah\n\n00:02:18,560 --> 00:02:29,811\nJauh dari sanak saudara\n\n00:02:29,760 --> 00:02:41,011\nhfoiqhwofhqiwfqwfqw hqwi fhquwfh huiqhwiufhqwf\n\n00:02:41,120 --> 00:02:52,371\nqowf hiqwhfuiqw fhqwf hwhuiqhw fhqwuifqw uqwf qwf qwf qwf\n\n00:02:52,320 --> 00:03:03,571\nqw oijoiwqjf oiqwj fijqwfi iqwhf iqhwfi hqwfqw fqw ifwf qw fqwf\n\n00:03:03,520 --> 00:03:14,771\nqw fojqwof jqwfjqw fqwf qwf qwf\n\"}\n\"}\n\"}\n\"}\n\"}\n\"}\n\"}\n', NULL, NULL, NULL, NULL, NULL, NULL, 1),
('11111', '1111', NULL, '11111', NULL, NULL, NULL, 165, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('111111', 'Bahagia Bersamamu', '123', 'aaaaaaaa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('111112', 'Bahagia Bersamamu', '123', 'aaaaaaaa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('11112', '11112', NULL, '11112', NULL, NULL, NULL, 200, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`album_id`);

--
-- Indexes for table `artist`
--
ALTER TABLE `artist`
  ADD PRIMARY KEY (`artist_id`);

--
-- Indexes for table `song`
--
ALTER TABLE `song`
  ADD PRIMARY KEY (`song_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

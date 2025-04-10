-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 08:54 PM
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
-- Database: `cosc360project`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `username` varchar(25) NOT NULL,
  `cover_image` varchar(100) DEFAULT NULL,
  `status` enum('available','borrowed','reserved') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `description`, `category`, `username`, `cover_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Guide to Java', 'This is a guide to learn Java', 'non-fiction', 'user1', './bookcovers/book_1742768340_user1.jpg', 'available', '2025-03-23 22:19:00', '2025-03-23 22:44:10'),
(2, 'Hitchhikers Guide to the Galaxy', 'An unassuming nobody accidentally hitches a ride on a passing spacecraft and adventures around the galaxy.', 'sci-fi', 'zubs', './bookcovers/book_1742777662_zubs.jpg', 'available', '2025-03-24 00:54:22', '2025-03-24 00:54:22');

-- --------------------------------------------------------

--
-- Table structure for table `book_requests`
--

CREATE TABLE `book_requests` (
  `request_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `requester_username` varchar(25) NOT NULL,
  `status` enum('pending','accepted','declined','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NULL DEFAULT NULL,
  `return_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_requests`
--

INSERT INTO `book_requests` (`request_id`, `book_id`, `requester_username`, `status`, `created_at`, `updated_at`, `request_date`, `response_date`, `return_date`) VALUES
(1, 1, 'User2', '', '2025-03-23 22:40:38', '2025-03-23 22:44:10', '2025-03-23 22:40:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `thread_id`, `username`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 'User2', 'I have it\'s great', '2025-03-23 22:42:57', '2025-03-23 22:42:57'),
(2, 2, 'user1', 'comment', '2025-03-23 23:09:08', '2025-03-23 23:09:08'),
(3, 2, 'User2', 'see this\n', '2025-03-23 23:10:11', '2025-03-23 23:10:11'),
(4, 3, 'zubs', 'adam testing async', '2025-03-24 01:04:23', '2025-03-24 01:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `threads`
--

CREATE TABLE `threads` (
  `thread_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `username` varchar(25) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `threads`
--

INSERT INTO `threads` (`thread_id`, `title`, `username`, `book_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 'Have you guys read this book', 'user1', NULL, 'I think it\'s super interesting', '2025-03-22 20:46:36', '2025-03-23 22:42:57'),
(2, 'AJAX thread test', 'user1', NULL, 'TESTING', '2025-03-23 23:08:12', '2025-03-23 23:10:11'),
(3, 'AJAX Listing Test', 'User2', NULL, 'Here', '2025-03-23 23:11:02', '2025-03-24 01:04:23'),
(4, 'TESTING FOR ADMIN DASHBAORD', 'zubs', NULL, 'just a test', '2025-04-08 03:13:41', '2025-04-08 03:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(25) NOT NULL,
  `password` varchar(33) NOT NULL,
  `email` varchar(25) NOT NULL,
  `firstname` varchar(25) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `profilepic` varchar(50) NOT NULL,
  `type` varchar(10) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `email`, `firstname`, `lastname`, `profilepic`, `type`) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@gmail.com', 'admin', '', './images/emptyprofilepic.jpg', 'admin'),
('user1', '24c9e15e52afc47c225b757e7bee1f9d', 'user1@gmail.com', 'user1', 'user1', './profilepics/user1.jpg', 'user'),
('User2', 'a09bccf2b2963982b34dc0e08d8b582a', 'user2@gmail.com', 'User2', '', './images/emptyprofilepic.jpg', 'user'),
('zubs', '146b2ea8e7521b80ca48e185ddf92fe8', 'zubs047@gmail.com', 'Adam', 'Zabenskie', './profilepics/zubs.jpg', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `idx_book_username` (`username`),
  ADD KEY `idx_book_category` (`category`),
  ADD KEY `idx_book_status` (`status`);

--
-- Indexes for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_book_requests_book_id` (`book_id`),
  ADD KEY `idx_book_requests_requester` (`requester_username`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_thread_id` (`thread_id`),
  ADD KEY `idx_comment_username` (`username`);

--
-- Indexes for table `threads`
--
ALTER TABLE `threads`
  ADD PRIMARY KEY (`thread_id`),
  ADD KEY `idx_thread_username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `book_requests`
--
ALTER TABLE `book_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `threads`
--
ALTER TABLE `threads`
  MODIFY `thread_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD CONSTRAINT `book_requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_requests_ibfk_2` FOREIGN KEY (`requester_username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `threads` (`thread_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `threads`
--
ALTER TABLE `threads`
  ADD CONSTRAINT `threads_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

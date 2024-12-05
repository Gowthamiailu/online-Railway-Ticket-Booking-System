-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2024 at 08:38 AM
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
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `train_id` int(11) NOT NULL,
  `ticket_category` varchar(20) NOT NULL,
  `num_tickets` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `train_id`, `ticket_category`, `num_tickets`, `total_amount`, `booking_date`, `created_at`) VALUES
(1, 9, 6, '0', 2, 1600.00, '2024-11-04 21:18:07', '2024-11-04 15:48:07'),
(2, 9, 3, '0', 1, 1200.00, '2024-11-05 09:32:32', '2024-11-05 04:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 9, 'overall the project is good but need some more easy way to use tahts it', '2024-11-04 13:59:09');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_name`, `created_at`) VALUES
(1, 'Mumbai', '2024-11-04 15:59:45'),
(2, 'Delhi', '2024-11-04 15:59:45'),
(3, 'Chennai', '2024-11-04 15:59:45'),
(4, 'Bangalore', '2024-11-04 15:59:45'),
(5, 'Hyderabad', '2024-11-04 15:59:45'),
(6, 'Pune', '2024-11-04 15:59:45'),
(7, 'Kolkata', '2024-11-04 15:59:45'),
(8, 'Goa', '2024-11-04 15:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `id` int(11) NOT NULL,
  `train_name` varchar(255) NOT NULL,
  `train_number` varchar(20) NOT NULL,
  `source_location` varchar(255) NOT NULL,
  `destination_location` varchar(255) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `first_class_price` decimal(10,2) NOT NULL,
  `second_class_price` decimal(10,2) NOT NULL,
  `general_price` decimal(10,2) NOT NULL,
  `first_class_seats` int(11) NOT NULL,
  `second_class_seats` int(11) NOT NULL,
  `general_seats` int(11) NOT NULL,
  `runs_on` varchar(255) DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`id`, `train_name`, `train_number`, `source_location`, `destination_location`, `departure_time`, `arrival_time`, `first_class_price`, `second_class_price`, `general_price`, `first_class_seats`, `second_class_seats`, `general_seats`, `runs_on`) VALUES
(1, 'Rajdhani Express', '12301', 'Delhi', 'Mumbai', '16:30:00', '08:00:00', 3500.00, 2200.00, 1100.00, 50, 100, 200, 'all'),
(2, 'Shatabdi Express', '12009', 'Mumbai', 'Pune', '06:25:00', '13:10:00', 2000.00, 1200.00, 500.00, 40, 80, 150, 'all'),
(3, 'Chennai Express', '12163', 'Chennai', 'Bangalore', '18:15:00', '16:30:00', 4000.00, 2500.00, 1200.00, 45, 90, 179, 'all'),
(4, 'Duronto Express', '12213', 'Hyderabad', 'Chennai', '10:20:00', '04:30:00', 3800.00, 2300.00, 1000.00, 40, 85, 160, 'all'),
(5, 'Humsafar Express', '12956', 'Delhi', 'Kolkata', '11:05:00', '14:30:00', 4500.00, 2800.00, 1300.00, 35, 75, 140, 'all'),
(6, 'Deccan Queen', '12123', 'Mumbai', 'Bangalore', '07:15:00', '10:25:00', 1500.00, 800.00, 300.00, 30, 60, 120, 'all'),
(7, 'Konkan Kanya', '10111', 'Mumbai', 'Goa', '23:05:00', '09:10:00', 2200.00, 1400.00, 600.00, 35, 70, 130, 'all'),
(8, 'Tejas Express', '22119', 'Bangalore', 'Mumbai', '06:40:00', '16:50:00', 3200.00, 2000.00, 900.00, 45, 90, 170, 'all');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('deposit','withdrawal','ticket_purchase','ticket_cancellation') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `type`, `created_at`) VALUES
(1, 9, 2000.00, 'deposit', '2024-11-04 13:46:54'),
(2, 9, 200.00, 'ticket_purchase', '2024-11-04 13:58:06'),
(3, 9, 200.00, 'ticket_cancellation', '2024-11-04 13:58:22'),
(4, 9, 100.00, 'deposit', '2024-11-04 14:09:18'),
(5, 9, 1000.00, 'deposit', '2024-11-04 16:01:10'),
(6, 9, 5000.00, 'deposit', '2024-11-04 16:01:15'),
(7, 9, 1000.00, 'deposit', '2024-11-04 16:01:22'),
(8, 9, 5000.00, 'deposit', '2024-11-04 16:01:37'),
(9, 9, 5000.00, 'deposit', '2024-11-04 16:04:55'),
(10, 9, 2000.00, 'deposit', '2024-11-04 16:15:35'),
(11, 9, 20000.00, 'deposit', '2024-11-05 03:53:40');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`) VALUES
(1, 'Ailu Gowthami', 'ailugowthami583@gmail.com', '$2y$10$Yk97cB0ezF3e37O8lyjrB.AYvT5lIZmn5MHvbm5YbnDf6cmU378pa');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'gowthami_ailu', '$2y$10$wItLHX14tlOXkrPQ1fvAceRVUhwHLnrwT992TwMv1MNmHLaoV3Ioi', 'ailugowthami583@gmail.com', '2024-10-25 10:14:39'),
(2, 'naveen', 'Naveen1907@', '', '2024-10-27 16:41:14'),
(9, 'siri', '$2y$10$G1ciTzCVlxAry5xdZonx3ui2mCCAIODONy669WUYnEK9W7qgX4fN6', '99220040416@klu.ac.in', '2024-11-04 13:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallet`
--

INSERT INTO `wallet` (`id`, `user_id`, `balance`) VALUES
(1, 9, 19200.00),
(2, 9, 19200.00),
(4, 9, 19200.00),
(5, 9, 19200.00),
(6, 9, 19200.00),
(7, 9, 19200.00),
(10, 9, 19200.00),
(12, 9, 19200.00),
(13, 9, 19200.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

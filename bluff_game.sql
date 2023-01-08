-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 08 Ιαν 2023 στις 20:53:40
-- Έκδοση διακομιστή: 10.4.24-MariaDB
-- Έκδοση PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `bluff_game`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `bluff`
--

DROP TABLE IF EXISTS `bluff`;
CREATE TABLE `bluff` (
  `id` int(11) NOT NULL,
  `card_number` varchar(255) NOT NULL,
  `card_style` enum('♦','♥','♣','♠') DEFAULT NULL,
  `actions` enum('played','bank') DEFAULT NULL,
  `actions_timestamp` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `game_status`
--

DROP TABLE IF EXISTS `game_status`;
CREATE TABLE `game_status` (
  `id` int(11) NOT NULL,
  `player_turn_id` int(11) NOT NULL,
  `first_winner_id` int(11) DEFAULT NULL,
  `second_winner_id` int(11) DEFAULT NULL,
  `last_change` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `room_id` int(11) NOT NULL,
  `num_of_cards_played` int(11) DEFAULT NULL,
  `value_of_cards_played` varchar(5) DEFAULT NULL,
  `played_by` int(11) DEFAULT NULL,
  `passes` int(11) NOT NULL,
  `game_ended` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `users_online` int(11) DEFAULT NULL,
  `status` enum('full','pending','empty') DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `log_in_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `token` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `bluff`
--
ALTER TABLE `bluff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Ευρετήρια για πίνακα `game_status`
--
ALTER TABLE `game_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_turn_id` (`player_turn_id`),
  ADD KEY `first_winner_id` (`first_winner_id`),
  ADD KEY `second_winner_id` (`second_winner_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Ευρετήρια για πίνακα `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `bluff`
--
ALTER TABLE `bluff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `game_status`
--
ALTER TABLE `game_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `bluff`
--
ALTER TABLE `bluff`
  ADD CONSTRAINT `bluff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bluff_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Περιορισμοί για πίνακα `game_status`
--
ALTER TABLE `game_status`
  ADD CONSTRAINT `game_status_ibfk_1` FOREIGN KEY (`player_turn_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `game_status_ibfk_2` FOREIGN KEY (`first_winner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `game_status_ibfk_3` FOREIGN KEY (`second_winner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `game_status_ibfk_4` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Περιορισμοί για πίνακα `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

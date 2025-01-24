-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 27. Dez 2024 um 15:12
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `africa`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(100) NOT NULL,
  `order_fk` int(11) NOT NULL,
  `booking_time` datetime NOT NULL,
  `status` enum('confirmed','cancelled') NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `bookings`
--

INSERT INTO `bookings` (`booking_id`, `order_fk`, `booking_time`, `status`, `user_id`) VALUES
(1, 0, '2024-12-31 20:30:00', 'confirmed', 2),
(2, 1, '2024-12-09 22:47:43', 'confirmed', 0),
(3, 2, '2024-12-09 22:49:56', 'confirmed', 0),
(4, 3, '2024-12-09 22:50:39', 'confirmed', 0),
(5, 0, '2024-12-25 20:00:00', 'confirmed', 2),
(6, 4, '2024-12-24 19:49:52', 'confirmed', 0),
(7, 0, '2024-12-25 20:00:00', 'confirmed', 3),
(8, 5, '2024-12-27 14:47:57', 'confirmed', 0),
(9, 6, '2024-12-27 14:56:00', 'confirmed', 0),
(10, 0, '2024-12-30 18:00:00', 'confirmed', 3),
(11, 0, '2024-12-27 15:00:00', 'confirmed', 1),
(12, 7, '2024-12-27 15:02:11', 'confirmed', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, '	Africa_Specialty'),
(2, 'Drinks'),
(3, 'Coffee'),
(4, 'bonbon'),
(6, 'plantain');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `comments`
--

INSERT INTO `comments` (`comment_id`, `order_id`, `user_id`, `comment`, `created_at`, `rating`) VALUES
(1, 1, 2, 'Lecker was das Essen', '2024-12-09 22:48:52', 5),
(2, 3, 2, 'humm', '2024-12-09 22:51:23', 5),
(3, 2, 2, 'lecker', '2024-12-24 14:50:36', 5),
(4, 4, 3, 'ausgezeichnet', '2024-12-24 19:56:56', 2),
(5, 6, 3, 'lecker', '2024-12-27 14:56:24', 4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` double DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `menu`
--

INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category_id`, `image_url`) VALUES
(1, 'Kuchen', 'kuchen mit Sahne', 15, 1, 'https://i.pinimg.com/originals/76/1f/2e/761f2e355ff864889e9d82cd03d6ca93.jpg'),
(2, 'Cola', 'American cola', 3.5, 2, 'https://th.bing.com/th/id/OIP.amD-fqUHOeQ734dEz6--7wHaHa?rs=1&pid=ImgDetMain'),
(3, 'Black-Coffee', 'Coffee with Milch and sugar', 4, 3, 'https://myfox8.com/wp-content/uploads/sites/17/2019/07/gettyimages-157774909.jpg?w=2121&h=1414&crop=1'),
(4, 'bonbon', 'au lait', 1, 4, 'https://www.monquotidienautrement.com/wp-content/uploads/2017/07/19391873408_8af93aab12_o.jpg'),
(5, 'alloco', 'alloco', 3, 6, 'https://i.pinimg.com/originals/c9/09/29/c90929e4b140409d1f1e4099111021bf.jpg');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `order_id` int(200) NOT NULL,
  `user_fk` int(200) NOT NULL,
  `table_fk` int(200) NOT NULL,
  `menu_fk` int(200) NOT NULL,
  `quantity` int(200) NOT NULL,
  `status` enum('placed','paid') NOT NULL,
  `order_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `orders`
--

INSERT INTO `orders` (`order_id`, `user_fk`, `table_fk`, `menu_fk`, `quantity`, `status`, `order_time`) VALUES
(1, 2, 1, 1, 1, 'paid', '2024-12-09 22:47:43'),
(2, 2, 1, 1, 1, 'paid', '2024-12-09 22:49:56'),
(3, 2, 1, 1, 3, 'paid', '2024-12-09 22:50:39'),
(4, 3, 1, 2, 3, 'paid', '2024-12-24 19:49:52'),
(5, 3, 1, 3, 6, 'placed', '2024-12-27 14:47:57'),
(6, 3, 3, 5, 2, 'paid', '2024-12-27 14:56:00'),
(7, 3, 2, 5, 1, 'paid', '2024-12-27 15:02:11');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('cash','online') NOT NULL,
  `payment_status` enum('pending','completed') NOT NULL,
  `payment_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `amount`, `payment_type`, `payment_status`, `payment_time`) VALUES
(1, 1, 15.00, 'cash', 'pending', '2024-12-09 22:48:06'),
(2, 3, 45.00, 'online', 'completed', '2024-12-09 22:51:07'),
(3, 2, 15.00, 'cash', 'pending', '2024-12-24 14:50:17'),
(4, 4, 10.50, 'online', 'completed', '2024-12-24 19:50:16'),
(5, 6, 6.00, 'online', 'completed', '2024-12-27 14:56:11'),
(6, 7, 3.00, 'cash', 'pending', '2024-12-27 15:02:18');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tables`
--

CREATE TABLE `tables` (
  `table_id` int(100) NOT NULL,
  `table_nr` int(100) NOT NULL,
  `status` enum('available','not') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `tables`
--

INSERT INTO `tables` (`table_id`, `table_nr`, `status`) VALUES
(1, 120, 'not'),
(2, 144, 'not'),
(3, 200, 'not');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Annie', 'annie10@gmail.com', '$2y$10$3ZtXETPUgatOWIE6.LDcf.Ke/jRFQcT9pTBQE..UeNjzmFUfOUcYW', 'admin', '2024-12-09 22:08:56'),
(2, 'sarah', 'sarah350@gmail.com', '', 'customer', '2024-12-09 22:09:20'),
(3, 'Sylvie', 'sisi@gmail.com', '$2y$10$Te13bAGYiPh.nTjhVZt.TeD8G0kJ6TQUR2u7q.vZlUqPAY5ZsS9xu', 'customer', '2024-12-24 19:16:27');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `order_fk` (`order_fk`);

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indizes für die Tabelle `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `table_fk` (`table_fk`),
  ADD KEY `user_id` (`user_fk`),
  ADD KEY `menu_fk` (`menu_fk`);

--
-- Indizes für die Tabelle `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`) USING BTREE,
  ADD KEY `order_id` (`order_id`);

--
-- Indizes für die Tabelle `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_id`),
  ADD UNIQUE KEY `table_id` (`table_id`),
  ADD UNIQUE KEY `table_nr` (`table_nr`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT für Tabelle `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `tables`
--
ALTER TABLE `tables`
  MODIFY `table_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints der Tabelle `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_fk`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`table_fk`) REFERENCES `tables` (`table_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`menu_fk`) REFERENCES `menu` (`menu_id`);

--
-- Constraints der Tabelle `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

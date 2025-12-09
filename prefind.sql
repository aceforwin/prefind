-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 09 Ara 2025, 19:42:01
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `prefind`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `riot_id` varchar(50) NOT NULL,
  `min_rank` varchar(20) NOT NULL,
  `max_rank` varchar(20) NOT NULL,
  `lobby_code` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `listings`
--

INSERT INTO `listings` (`id`, `user_id`, `riot_id`, `min_rank`, `max_rank`, `lobby_code`, `note`, `is_active`, `created_at`) VALUES
(12, 5, 'sayonara#tr1', 'Iron', 'Gold', 'GLAEQE', '', 1, '2025-12-09 05:35:05');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','answered','closed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `name`, `email`, `subject`, `message`, `attachment`, `status`, `created_at`) VALUES
(1, 'astekek', 'astekek@gmail.com', 'account', 'hesabımda profil fotoğrafı değişmiyor.', 'help_6937b3fede0559.66964880.jpg', 'answered', '2025-12-09 08:30:38'),
(2, 'astekek', 'astekek@gmail.com', 'account', 'hesabımda profil fotoğrafı değişmiyor.', 'help_6937b4135ef9a2.19230690.jpg', 'pending', '2025-12-09 08:30:59');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(50) DEFAULT NULL,
  `tracker_link` varchar(255) DEFAULT NULL,
  `discord_username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `google_id`, `name`, `email`, `avatar`, `created_at`, `username`, `tracker_link`, `discord_username`) VALUES
(1, '103430838370305815421', 'Enver Taş', 'otxku.dev@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocILEqe794dxuomK6UHHXWAvYRFlfTwyowu9J7ocgEug0I1Mrg=s96-c', '2025-12-09 03:31:56', 'lol', NULL, NULL),
(2, '116410789979155584092', 'Okan Güler', 'okannsizeyeter12z@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocKx_vMEwpzq9xcnxOYqPxqG_yaii5JzSELVcW1R7KQbr4QImw=s96-c', '2025-12-09 03:34:03', 'acekunda', NULL, NULL),
(3, '111867039910118313478', 'Memories', 'enverblade@gmail.com', 'https://lh3.googleusercontent.com/a-/ALV-UjUnTD4uY5ECV_JUjdmv3jjAF5JiisVVqLbMdeKNDVezNvTEbwufZYTun7groaIFkzfMn_2FZkpewuMjNrC6pwXKnBXnUs32Nae2YypT50Uv2RAX39B31dHEU4_QUN5SJ-H5Z1M_yt1Lz2Y8kwIHUGQkaxjuyYhJPMFdGBLu0wKn8oQJBtDDNb3FkmDh9pFRJzEYvY2cYM_6Q-wfxotQnrtmzNY60OXn_QF3Vbx2AP9RQUz8T5DAMzQ_zUIFF2BVxwpv5GaH5UASsqKWEcn1GVIQ4BHN49vfkiby2hfYrZUiYTk6woOlNE-eKzq_3ckfYIpcQHElb1pSHzRAptxjWL4LM5xj4MJZXu_stdhBewp0b2TZKKF0hcIGGRgWyeQRJzITfg7Jd8DCpyaTn7qI9XFITyN8PS95YaP6Mux7xLZpQjEwAdDXhQP2gJuH4BBFTwWDuXJ9t2haalHnFUQRcKgoisfLVUOYjIaJAN4eXdJzBPtOodEzwNsu2ktSoVWr9H2dXX6v8ejpn5k7FIhkMxWAaFvZt2jYBlX-aS6uVS_LKtE6GNlJOHEJ191Dp5qDXqahHyE4kiJpPyibW12E9VlTpCKjBRkQ4OrTdrEOFvEs5T1QZBBNuec-iDhm-lvLjU4x7uNJEQHKF9F-nIDDAjefPHclm-k_SpiFisjnP4S-YBkmTBlN-RyltO1yRglsNnC1SJemQmj5DFWh_LvWdJz4Oz5jp5RAzvNv03n4kF_Svr2SnYbUAvXyH3xOoyqYfkq7X3hD3GUZ_PwLG5yU4Dx-CWh2QH9zT3mD5Q4hM17yrGVO5R-SH6V0cRlCLIzxNK_W6PsNreApPWP_HBC6dxxulV2eFSLwpSB9YZyWo9ZckOlgGDJk_TPu3daldoVu1eslEmiwIOkCSbE9oGhjdKnvipgC3KbSwycSyPhSc7XRoJEodW9fEVNAL0lToz0AktXNICkhbRJlnJmFVsVTCu_Hh7hAjGXnQWBcPEnTfuFK2AiwSCKlH8gNpC9gyn_cDqhJ9su_1U8K-J887OJ6EnoXNQZJdx81QDvUD5DvLIkGB9rSJsUj=s96-c', '2025-12-09 03:56:13', NULL, NULL, NULL),
(4, '113249204116199945971', 'bigL', '901bigl@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocK-TlTGiLl_nMrLYIxMAdIU-Wru7wcy09pX4HYTw4-nbb-F2g=s96-c', '2025-12-09 03:56:24', NULL, NULL, NULL),
(5, '103185535263130417936', 'Enver Taş', 'enverdevs@gmail.com', 'uploads/user_5_1765254990.jpeg', '2025-12-09 04:04:27', 'ace', 'https://tracker.gg/valorant/profile/riot/Bumbibj%C3%B6rn1%23demi/overview?platform=pc&playlist=competitive&season=4c4b8cff-43eb-13d3-8f14-96b783c90cd2', 'kullanici'),
(6, '111876648863253157976', 'Sıla Taş', 'silaataas@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocL9NEi06eCOWUs46HQgc3ohHATQM0XZrdx-50C9X43UdNuPKQ=s96-c', '2025-12-09 04:42:54', 'haha', NULL, NULL),
(7, '103752199018406977562', 'Gemini gemniaa', 'geminiaccla@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocLluFTlL4bZExUnzfiWgbCRt4V6b4peluINUb67an6BMNniKw=s96-c', '2025-12-09 04:56:44', 'acek', NULL, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

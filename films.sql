-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 28 mrt 2025 om 11:27
-- Serverversie: 10.4.28-MariaDB
-- PHP-versie: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `films`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cast_members`
--

CREATE TABLE `cast_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `film_id` int(11) NOT NULL,
  `character_name` varchar(255) DEFAULT NULL,
  `order_in_credits` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `cast_members`
--

INSERT INTO `cast_members` (`id`, `name`, `image_url`, `film_id`, `character_name`, `order_in_credits`) VALUES
(5, 'Faze sway', 'https://img.uxcel.com/practices/settings-content-1602859197873/a-1703087591286-2x.jpg', 37, 'fraudeur', NULL),
(6, 'arda', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTk_PSdJvGh9pdLp60EHXKM06ACM2lnd0KruA&s', 38, 'arda', NULL),
(7, 'theiiry henry', 'https://img.uxcel.com/practices/settings-content-1602859197873/a-1703087591286-2x.jpg', 37, 'gqkew', NULL),
(8, 'Thierry Henry', 'https://upload.wikimedia.org/wikipedia/commons/b/b9/Thierry_Henry_%2851649035951%29_%28cropped%29.jpg', 39, 'appa yip', 1),
(10, 'arda', 'https://static1.colliderimages.com/wordpress/wp-content/uploads/2023/04/stephen-amell-green-arrow-oliver-queen.jpg?q=50&fit=crop&w=1140&h=&dpr=1.5', 41, 'arda', 1),
(11, 'hoi', 'https://m.media-amazon.com/images/M/MV5BNjRlNjNlY2YtYzQxNS00ZTUzLTkwMTQtMjM0YjZlOWQwZmFkXkEyXkFqcGc@._V1_.jpg', 39, 'kaas', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `films`
--

CREATE TABLE `films` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `url_trailer` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `votes` int(11) DEFAULT 0,
  `timestamp` bigint(20) DEFAULT NULL,
  `date` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `films`
--

INSERT INTO `films` (`id`, `title`, `description`, `category`, `url_trailer`, `image_url`, `votes`, `timestamp`, `date`) VALUES
(22, 'testing', 'test', 'test', 'https://www.youtube.com/@enzoknoltwee', '/images/1740753005285.jpg', 0, 1739538512252, 1739538512252),
(24, 'Spiderman', 'Over een man die zich spiderman noemt', 'action', 'https://www.youtube.com/watch?v=JfVOs4VSpmA', '/images/1740750203099.jpg', 0, 1739520386668, 1739520387095),
(25, 'Superman', 'Echt een held', 'action', 'https://www.youtube.com/watch?v=T6DJcgm3wNY', '/images/1740750225982.jpg', 1, 1739520013066, 1739520013231),
(26, 'Garfield', 'Garfield', 'Comedy', 'https://www.youtube.com/watch?v=IeFWNtMo1Fs', '/images/1740753005285.jpg', 0, 1699973460183, 1699973460183),
(27, 'Superman', 'Echt een held', 'action', 'https://www.youtube.com/watch?v=T6DJcgm3wNY', '/images/1740749381321.jpg', 0, 1739520014359, 1739520014975),
(28, 'Superman', 'Echt een held', 'action', 'https://www.youtube.com/watch?v=T6DJcgm3wNY', '/images/1740750203099.jpg', 0, 1739520386670, 1739520386936),
(29, 'Starwars', 'Starwars', 'Scify', 'https://www.youtube.com/watch?v=NDKRtjCEvVA', '/images/1740750225982.jpg', 1, 1699973460368, 1699973460368),
(30, 'Commando', 'Commando', 'Action', 'https://www.youtube.com/watch?v=m264f4tfG2s', '/images/1740753005285.jpg', 1, 1699973460339, 1699973460339),
(31, 'Superman', 'Echt een held', 'action', 'https://www.youtube.com/watch?v=T6DJcgm3wNY', '/images/1740749381321.jpg', 0, 1739520387504, 1739520387512),
(32, 'Spiderman', 'Over een man die zich spiderman noemt', 'action', 'https://www.youtube.com/watch?v=JfVOs4VSpmA', '/images/1740750225982.jpg', 0, 1739520449769, 1739520449909),
(33, 'hoi', 'hoi', 'Acton', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUSEhIVFRUVFhgWFRcVFxcVFRUVGBYXGBUVFxUYHSggGBolHRUXITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQFy0lHx0tLS0rKy0tLS0tLS0tLS0tKy0tLSstLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAPkAygMBIgACEQEDEQH/', 0, 1742549377000, 1742549377000),
(34, 'hoiww', 'hoi', 'Acton', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUSEhIVFRUVFhgWFRcVFxcVFRUVGBYXGBUVFxUYHSggGBolHRUXITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQFy0lHx0tLS0rKy0tLS0tLS0tLS0tKy0tLSstLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAPkAygMBIgACEQEDEQH/', 0, 1742549573000, 1742549573000),
(35, 'oaa11', 'qkwenq', 'lkwejrklj', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'https://img.uxcel.com/practices/settings-content-1602859197873/a-1703087591286-2x.jpg', 0, 1742549652000, 1742549652000),
(36, 'farhan', 'hoi', 'action', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'https://img.uxcel.com/practices/settings-content-1602859197873/a-1703087591286-2x.jpg', 0, 1742550271, 2025),
(37, 'farhan', 'hoi', 'hoi', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'https://img.uxcel.com/practices/settings-content-1602859197873/a-1703087591286-2x.jpg', 0, 1742550389, 2025),
(38, 'faze arda', 'ass in rocket league', 'action', 'https://www.youtube.com/shorts/c6AAAvI2Gbk', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTeF2geh0SqP8QYPy7If90lf6eruiEYXlv-3A&s', 0, 1742550464, 2025),
(39, 'The Flash S7 is peak', 'downfall van the flash is crazy', 'Action', 'https://www.youtube.com/watch?v=nrHqvUXyqJc&pp=ygUVZG93bmZhbGwgb2YgdGhlIGZsYXNo', 'https://static.dc.com/dc/files/default_images/Char_Profile_Flash_20190116_5c3fcaaa18f0e8.03668117.jpg', 0, 1743116887000, 2025),
(41, 'the arrow s8', 'peak', 'Actie', 'https://www.youtube.com/watch?v=MfuUqxV8Wec', 'https://m.media-amazon.com/images/M/MV5BNjRlNjNlY2YtYzQxNS00ZTUzLTkwMTQtMjM0YjZlOWQwZmFkXkEyXkFqcGc@._V1_.jpg', 2, 1743148731000, 2025),
(42, 'arda\'s leven', 'zijn e\\leven is fanat\\\\tas\\\\', 'action', 'https://www.youtube.com/watch?v=INzL9vNnos4&embeds_referring_euri=https%3A%2F%2Fwww.bing.com%2F&embeds_referring_origin=https%3A%2F%2Fwww.bing.com&source_ve_path=Mjg2NjY', 'https://www.bing.com/images/search?view=detailV2&ccid=5trtsIn%2f&id=80040841987E3839DF62B58F62AE65767FD1AD2C&thid=OIP.5trtsIn_l85ZuKNjGHCn0QHaE5&mediaurl=https%3a%2f%2fmediaim.expedia.com%2fdestination%2f1%2f8b35f3641c6ea86e64d957d148ec6375.jpg&cdnurl=htt', 0, 1743154711000, 2025);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT 'uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`, `created_at`, `avatar`) VALUES
(1, 'hoi', 'hoi', 1, '2025-03-14 09:22:47', 'img/avatar.png'),
(2, 'wavezy', '$2y$10$2f12xNCLhoqDNU1OdR6aUuo5i.k3tT3P7RAc3NLgMxKGKDcwRrdXy', 0, '2025-03-21 08:21:19', 'img/avatar.png'),
(3, 'kaas', '$2y$10$KH9akFSSTOZSjR4/wUHfb.i5HrSPnjF1I9SjIUW18rsqJEtLRJylK', 0, '2025-03-21 08:56:16', 'img/avatar.png'),
(5, 'yoo', '$2y$10$r.ZGCPTuFEFFi9uA4Yyd5O8lA0ZoTmLqWP/ZQYdTCEwYLR12RoO0u', 0, '2025-03-21 09:04:52', 'img/avatar.png'),
(6, 'paa', '$2y$10$14VB5JQFztmU81LUbVGLKucc3Yxf/U0x0mXvzdBMjFWRpCVPHLNtm', 0, '2025-03-21 09:07:47', 'img/avatar.png'),
(7, 'iaa', '$2y$10$BlfeOPhc9G2Yb.5jaZcONeiQ3Wsll.LG7VTbjUjonRIDBG6scWiLe', 0, '2025-03-21 09:08:16', 'img/avatar.png'),
(8, 'goo', '$2y$10$2ATfkHjozEVQgDYC.GwCRuiG/cgNScfPD/l.6X4hJvVAJ0QS2tSbm', 0, '2025-03-21 09:09:31', 'img/avatar.png'),
(9, 'farhana', '$2y$10$G3nSSlrzoLdRa0gnaPoApemYXj936JuJ9WlNNOdeY01KWhJC76q3S', 0, '2025-03-21 09:14:14', 'img/avatar.png'),
(10, 'yo', '$2y$10$whOlcxxexwoJ7h42fxD4Ouca7APUPr93upqo1gknNMFQyjoByqOpu', 0, '2025-03-25 10:37:54', 'img/avatar.png'),
(11, 'baaa', '$2y$10$Ayc9hGkV4eLyqjsZf.Ev3u8jgRGst98KDKfBt6332546tEyi9c2S6', 0, '2025-03-25 10:39:34', 'img/avatar.png'),
(12, 'testing01', '$2y$10$7Y4xigTbFQYAF0FwTmW6XO75T/8FU20jK5QATOL/XLu80yw8kDcmG', 0, '2025-03-27 19:54:24', 'img/avatar.png'),
(13, 'testingjaa', '$2y$10$CuqFNEYEbhLTGMSpSPOCiey4yXYf.jsz/GrNN0fiFC47Amjk/7.0q', 0, '2025-03-27 20:09:54', 'img/avatar.png'),
(14, 'testing011', '$2y$10$1j/uSj.ypG8c7wvngMCzAeOU91Gz0GIvpHvxcaXUvX/.YPlOvXQcy', 0, '2025-03-27 20:19:49', 'uploads/1743107323_2048px-Default_pfp.svg_png.png'),
(15, 'hoihoi', '$2y$10$ordsSpgUzILHyj55o64VHuTwCGmyHgQZmUDXQhTgUXYr8MhEGgao2', 0, '2025-03-27 20:33:14', 'uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png'),
(16, 'aa', '$2y$10$Reofa3goaFgFzNaZRsE78uqVmca8S0ST/QxCi2DQ/9xVm6hktPJGu', 0, '2025-03-27 20:34:25', 'uploads/1743107975_tumblr_ecae15989a0bac87feff13783ac88ffc_175cc43f_1280.png'),
(17, 'wavez', 'boom', 1, '2025-03-27 20:42:54', 'uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png'),
(18, 'wakemeup', '$2y$10$L9v8M.bMBSH.pflpRp9WPeX7wtbhO5WkoVRyqn3Hgv2Vqpu9eTxvu', 1, '2025-03-27 21:06:08', 'uploads/avatars/1743107323_2048px-Default_pfp.svg_png.png'),
(19, 'jantje', '$2y$10$5ijtSloI/w3w2INrJQsTV.I8Km/x4oy.Jb.ljGFKLbsfLA3vGbqv.', 0, '2025-03-27 23:14:22', 'uploads/1743117293_tumblr_ecae15989a0bac87feff13783ac88ffc_175cc43f_1280.png');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `cast_members`
--
ALTER TABLE `cast_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `film_id` (`film_id`);

--
-- Indexen voor tabel `films`
--
ALTER TABLE `films`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexen voor tabel `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`movie_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `cast_members`
--
ALTER TABLE `cast_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT voor een tabel `films`
--
ALTER TABLE `films`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT voor een tabel `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `cast_members`
--
ALTER TABLE `cast_members`
  ADD CONSTRAINT `cast_members_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `films` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

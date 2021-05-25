-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 25 Maj 2021, 09:45
-- Wersja serwera: 10.4.17-MariaDB
-- Wersja PHP: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `countries`
--

DELIMITER $$
--
-- Procedury
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `dodajWezel` (IN `nazwa` VARCHAR(255), `rodzic` INT(1))  INSERT INTO `countries` (`id`, `text`, `parent_id`) VALUES (NULL, nazwa, rodzic)$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usunWezel` (IN `id` INT(11))  NO SQL
DELETE FROM countries WHERE id=id$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `zmienNazwe` (IN `nazwa` VARCHAR(255), `id` INT(11))  UPDATE `countries` SET `text` = nazwa WHERE `countries`.`id` = id$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `znajdzDzieci` (IN `id` INT(11))  NO SQL
SELECT * FROM countries WHERE parent_id=id$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `text` varchar(250) CHARACTER SET latin1 NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `countries`
--

INSERT INTO `countries` (`id`, `text`, `parent_id`) VALUES
(1, 'Countries', 0),
(2, 'Europe', 1),
(3, 'Australia', 1),
(4, 'South America', 1),
(5, 'North America', 1),
(6, 'Asia', 1),
(7, 'Africa', 1),
(8, 'Poland', 2),
(9, 'Warszawa', 8),
(10, 'Lublin', 8),
(11, 'Kraków', 8),
(12, 'Canada', 5),
(13, 'Mexico', 5),
(14, 'Argentina', 4),
(15, 'Brazil', 4),
(16, 'Chile', 4),
(17, 'Germany', 2),
(18, 'France', 2),
(19, 'Canberra', 3),
(20, 'Sydney', 3),
(21, 'Hong Kong', 6),
(22, 'Japan', 6),
(23, 'India', 6),
(24, 'Tanzania', 7),
(25, 'Egypt', 7),
(26, 'Ethiopia', 7),
(27, 'United States', 5),
(28, 'New York', 27),
(29, 'Washington', 27),
(30, 'Dairut', 25),
(31, 'Giza', 25);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `text_UNIQUE` (`text`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

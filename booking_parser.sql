-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 08 2022 г., 19:56
-- Версия сервера: 10.3.13-MariaDB-log
-- Версия PHP: 7.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `booking_parser`
--

-- --------------------------------------------------------

--
-- Структура таблицы `constants`
--

CREATE TABLE `constants` (
  `Guesthouse_Kuin_Kotonaan` int(11) NOT NULL,
  `Hotel_Leikari` int(11) NOT NULL,
  `Leikari_Nature_Bungalows_with_Terrace` int(11) NOT NULL,
  `Apartments` int(11) NOT NULL,
  `Kotkan_Residenssi_Apartments` int(11) NOT NULL,
  `Guest_House_Nina_Art` int(11) NOT NULL,
  `Guesthouse_Lokinlaulu` int(11) NOT NULL,
  `The_Grand_Karhu` int(11) NOT NULL,
  `Kartanohotelli_Karhulan_Hovi` int(11) NOT NULL,
  `Kartanohotelli_Karhulan_Hovi_Sunday` int(11) NOT NULL,
  `Hotelli_Merikotka` int(11) NOT NULL,
  `Hotelli_Kotola` int(11) NOT NULL,
  `Kesähostelli_Kärkisaari` int(11) NOT NULL,
  `Hotel_Villa_Vanessa` int(11) NOT NULL,
  `Beach_Hotel_Santalahti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `constants`
--

INSERT INTO `constants` (`Guesthouse_Kuin_Kotonaan`, `Hotel_Leikari`, `Leikari_Nature_Bungalows_with_Terrace`, `Apartments`, `Kotkan_Residenssi_Apartments`, `Guest_House_Nina_Art`, `Guesthouse_Lokinlaulu`, `The_Grand_Karhu`, `Kartanohotelli_Karhulan_Hovi`, `Kartanohotelli_Karhulan_Hovi_Sunday`, `Hotelli_Merikotka`, `Hotelli_Kotola`, `Kesähostelli_Kärkisaari`, `Hotel_Villa_Vanessa`, `Beach_Hotel_Santalahti`) VALUES
(13, 55, 10, 8, 13, 4, 4, 19, 9, 8, 10, 8, 6, 12, 12);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

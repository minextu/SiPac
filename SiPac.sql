-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. Jul 2013 um 13:18
-- Server Version: 5.5.27
-- PHP-Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `chatengine`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chat_entries`
--

CREATE TABLE IF NOT EXISTS `chat_entries` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `user` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `extra` int(3) NOT NULL,
  `highlight` varchar(10000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL,
  `channel` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `chat_id` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=50664 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chat_users`
--

CREATE TABLE IF NOT EXISTS `chat_users` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `action` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `info` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `style` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `afk` int(2) NOT NULL,
  `writing` int(2) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `last_time` int(10) NOT NULL,
  `channel` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `chat_id` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9003 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

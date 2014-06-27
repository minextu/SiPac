SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `sipac_entries` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `user` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` int(3) NOT NULL,
  `style` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL,
  `channel` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `chat_id` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=55254 ;

CREATE TABLE IF NOT EXISTS `sipac_users` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `task` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `info` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `style` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `afk` int(2) NOT NULL,
  `writing` int(2) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `online` int(10) NOT NULL,
  `channel` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `chat_id` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1112 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

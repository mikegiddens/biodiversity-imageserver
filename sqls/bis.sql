-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 18, 2012 at 11:58 AM
-- Server version: 5.5.20
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bis_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `bis2hs`
--

CREATE TABLE IF NOT EXISTS `bis2hs` (
  `image_id` int(11) NOT NULL,
  `filename` varchar(60) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `imageserver_id` int(11) DEFAULT NULL,
  `timestamp_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE IF NOT EXISTS `collection` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `collectionSize` int(11) NOT NULL,
  PRIMARY KEY (`collection_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `evenote_accounts`
--

CREATE TABLE IF NOT EXISTS `evenote_accounts` (
  `enAccountId` int(11) NOT NULL AUTO_INCREMENT,
  `accountName` varchar(50) NOT NULL,
  `username` varchar(60) CHARACTER SET latin1 NOT NULL,
  `password` varchar(60) CHARACTER SET latin1 NOT NULL,
  `consumerKey` varchar(100) CHARACTER SET latin1 NOT NULL,
  `consumerSecret` varchar(100) CHARACTER SET latin1 NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `dateAdded` timestamp NULL DEFAULT NULL,
  `dateModified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`enAccountId`),
  UNIQUE KEY `accountName` (`accountName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `eventId` int(11) NOT NULL AUTO_INCREMENT,
  `geoId` int(11) NOT NULL,
  `eventDate` datetime NOT NULL,
  `eventTypeId` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  PRIMARY KEY (`eventId`),
  KEY `geoId` (`geoId`,`eventTypeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE IF NOT EXISTS `event_types` (
  `eventTypeId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  `modifiedTime` datetime NOT NULL,
  PRIMARY KEY (`eventTypeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `geography`
--

CREATE TABLE IF NOT EXISTS `geography` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(40) NOT NULL,
  `country_iso` varchar(3) NOT NULL,
  `admin_0` varchar(255) NOT NULL,
  `admin_1` varchar(255) NOT NULL,
  `admin_2` varchar(255) NOT NULL,
  `admin_3` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country` (`country`,`country_iso`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(60) NOT NULL,
  `timestamp_modified` datetime DEFAULT NULL,
  `barcode` varchar(20) DEFAULT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `Family` varchar(20) DEFAULT NULL,
  `Genus` varchar(20) DEFAULT NULL,
  `SpecificEpithet` varchar(20) DEFAULT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  `author` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `GlobalUniqueIdentifier` char(30) NOT NULL,
  `creative_commons` varchar(10) NOT NULL DEFAULT 'by-nc',
  `characters` text NOT NULL,
  `flickr_PlantID` bigint(20) DEFAULT NULL,
  `flickr_modified` datetime DEFAULT NULL,
  `flickr_details` varchar(60) DEFAULT NULL,
  `picassa_PlantID` bigint(30) DEFAULT NULL,
  `picassa_modified` datetime DEFAULT NULL,
  `gTileProcessed` tinyint(4) NOT NULL DEFAULT '0',
  `zoomEnabled` tinyint(4) NOT NULL DEFAULT '0',
  `processed` tinyint(4) NOT NULL DEFAULT '0',
  `box_flag` tinyint(4) NOT NULL DEFAULT '0',
  `ocr_flag` tinyint(4) NOT NULL DEFAULT '0',
  `ocr_value` text NOT NULL,
  `namefinder_flag` tinyint(4) NOT NULL DEFAULT '0',
  `namefinder_value` text NOT NULL,
  `ScientificName` varchar(30) NOT NULL,
  `CollectionCode` varchar(10) NOT NULL,
  `CatalogueNumber` int(11) NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `Family` (`Family`),
  KEY `Genus` (`Genus`),
  KEY `ScientificName` (`ScientificName`),
  KEY `Barcode` (`barcode`),
  KEY `CatalogueNumber` (`CatalogueNumber`),
  KEY `CollectionCode` (`CollectionCode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `image_attrib`
--

CREATE TABLE IF NOT EXISTS `image_attrib` (
  `imageID` int(11) NOT NULL DEFAULT '0',
  `typeID` int(11) NOT NULL DEFAULT '0',
  `valueID` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `u_index` (`imageID`,`typeID`,`valueID`),
  KEY `imageID` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Table structure for table `image_attrib_type`
--

CREATE TABLE IF NOT EXISTS `image_attrib_type` (
  `typeID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`typeID`),
  UNIQUE KEY `attribID` (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `image_attrib_value`
--

CREATE TABLE IF NOT EXISTS `image_attrib_value` (
  `valueID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `typeID` int(11) DEFAULT NULL,
  PRIMARY KEY (`valueID`),
  UNIQUE KEY `valueID` (`valueID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `image_log`
--

CREATE TABLE IF NOT EXISTS `image_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(11) DEFAULT NULL,
  `before_desc` varchar(255) DEFAULT NULL,
  `after_desc` varchar(255) DEFAULT NULL,
  `query` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `im_log`
--

CREATE TABLE IF NOT EXISTS `im_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(11) DEFAULT NULL,
  `before_desc` varchar(255) DEFAULT NULL,
  `after_desc` varchar(255) DEFAULT NULL,
  `query` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 33792 kB' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(20) NOT NULL,
  `table` varchar(40) NOT NULL,
  `query` text NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  `modifiedTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `master_log`
--

CREATE TABLE IF NOT EXISTS `master_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sc_id` varchar(20) NOT NULL,
  `log_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `before` blob,
  `after` blob,
  `task` varchar(40) NOT NULL,
  `timestamp_modified` datetime DEFAULT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`sc_id`,`log_id`,`station_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `process_queue`
--

CREATE TABLE IF NOT EXISTS `process_queue` (
  `image_id` varchar(40) NOT NULL DEFAULT '',
  `process_type` varchar(20) NOT NULL,
  `extra` text,
  `date_added` datetime DEFAULT NULL,
  `processed` datetime DEFAULT NULL,
  `errors` tinyint(4) DEFAULT '0',
  `error_details` blob,
  PRIMARY KEY (`image_id`,`process_type`),
  KEY `Barcode` (`image_id`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `specimen2label`
--

CREATE TABLE IF NOT EXISTS `specimen2label` (
  `labelId` int(11) NOT NULL,
  `evernoteAccountId` int(11) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `dateAdded` datetime NOT NULL,
  UNIQUE KEY `labelId` (`labelId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(25) NOT NULL DEFAULT '',
  `pw` varchar(32) NOT NULL DEFAULT '',
  `real_name` varchar(32) NOT NULL DEFAULT '',
  `extra_info` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `tmp_mail` varchar(50) NOT NULL DEFAULT '',
  `access_level` tinyint(4) NOT NULL DEFAULT '0',
  `active` enum('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`login`),
  UNIQUE KEY `mail` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE IF NOT EXISTS `user_permissions` (
  `userId` int(11) NOT NULL,
  `event` varchar(50) NOT NULL,
  `C` tinyint(4) NOT NULL,
  `R` tinyint(4) NOT NULL,
  `U` tinyint(4) NOT NULL,
  `D` tinyint(4) NOT NULL,
  `G` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

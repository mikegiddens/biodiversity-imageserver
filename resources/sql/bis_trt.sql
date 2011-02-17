-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 17, 2011 at 08:07 AM
-- Server version: 5.1.47
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bis_trt`
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
  `flickr_PlantID` bigint(20) DEFAULT NULL,
  `flickr_modified` datetime DEFAULT NULL,
  `flickr_details` varchar(60) DEFAULT NULL,
  `picassa_PlantID` bigint(30) DEFAULT NULL,
  `picassa_modified` datetime DEFAULT NULL,
  `gTileProcessed` tinyint(4) NOT NULL DEFAULT '0',
  `zoomEnabled` tinyint(4) NOT NULL DEFAULT '0',
  `processed` tinyint(4) NOT NULL DEFAULT '0',
  `ocr_flag` tinyint(4) NOT NULL DEFAULT '0',
  `ocr_value` text NOT NULL,
  `namefinder_flag` tinyint(4) NOT NULL DEFAULT '0',
  `namefinder_value` text NOT NULL,
  `ScientificName` varchar(30) NOT NULL,
  `CollectionCode` varchar(10) NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `Family` (`Family`),
  KEY `Genus` (`Genus`),
  KEY `ScientificName` (`ScientificName`),
  KEY `Barcode` (`barcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

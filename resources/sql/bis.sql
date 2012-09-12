-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 13, 2012 at 04:47 AM
-- Server version: 5.5.24
-- PHP Version: 5.3.14

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
-- Table structure for table `bis2Hs`
--

CREATE TABLE IF NOT EXISTS `bis2Hs` (
  `imageId` int(11) NOT NULL,
  `filename` varchar(60) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `clientId` int(11) DEFAULT NULL,
  `collectionId` int(11) DEFAULT NULL,
  `imageServerId` int(11) DEFAULT NULL,
  `timestampModified` datetime DEFAULT NULL,
  PRIMARY KEY (`imageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE IF NOT EXISTS `collection` (
  `collectionId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(60) NOT NULL,
  `collectionSize` int(11) NOT NULL,
  PRIMARY KEY (`collectionId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `evenoteAccounts`
--

CREATE TABLE IF NOT EXISTS `evenoteAccounts` (
  `enAccountId` int(11) NOT NULL AUTO_INCREMENT,
  `accountName` varchar(50) NOT NULL,
  `userName` varchar(60) CHARACTER SET latin1 NOT NULL,
  `password` varchar(60) CHARACTER SET latin1 NOT NULL,
  `consumerKey` varchar(100) CHARACTER SET latin1 NOT NULL,
  `consumerSecret` varchar(100) CHARACTER SET latin1 NOT NULL,
  `notebookGuid` varchar(100) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `dateAdded` timestamp NULL DEFAULT NULL,
  `dateModified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`enAccountId`),
  UNIQUE KEY `accountName` (`accountName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `eventId` int(11) NOT NULL AUTO_INCREMENT,
  `geographyId` int(11) NOT NULL,
  `eventDate` datetime NOT NULL,
  `eventTypeId` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  PRIMARY KEY (`eventId`),
  KEY `geographyId` (`geographyId`,`eventTypeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `eventImages`
--

CREATE TABLE IF NOT EXISTS `eventImages` (
  `eventImageId` int(11) NOT NULL AUTO_INCREMENT,
  `imageId` int(11) NOT NULL,
  `eventId` int(11) NOT NULL,
  PRIMARY KEY (`eventImageId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `eventTypes`
--

CREATE TABLE IF NOT EXISTS `eventTypes` (
  `eventTypeId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  `modifiedTime` datetime NOT NULL,
  PRIMARY KEY (`eventTypeId`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `evernoteTags`
--

CREATE TABLE IF NOT EXISTS `evernoteTags` (
  `evernoteTagId` int(11) NOT NULL AUTO_INCREMENT,
  `tagName` varchar(100) NOT NULL,
  `tagGuid` varchar(100) NOT NULL,
  PRIMARY KEY (`evernoteTagId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geography`
--

CREATE TABLE IF NOT EXISTS `geography` (
  `geographyId` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(40) NOT NULL,
  `countryIso` varchar(3) NOT NULL,
  `admin0` varchar(255) NOT NULL,
  `admin1` varchar(255) NOT NULL,
  `admin2` varchar(255) NOT NULL,
  `admin3` varchar(255) NOT NULL,
  PRIMARY KEY (`geographyId`),
  KEY `country` (`country`,`countryIso`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `imageId` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(60) NOT NULL,
  `timestampModified` datetime DEFAULT NULL,
  `barcode` varchar(20) DEFAULT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `family` varchar(20) DEFAULT NULL,
  `genus` varchar(20) DEFAULT NULL,
  `specificEpithet` varchar(20) DEFAULT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  `author` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `globalUniqueIdentifier` char(30) DEFAULT NULL,
  `creativeCommons` varchar(10) NOT NULL DEFAULT 'by-nc',
  `characters` text,
  `flickrPlantId` bigint(20) DEFAULT NULL,
  `flickrModified` datetime DEFAULT NULL,
  `flickrDetails` varchar(60) DEFAULT NULL,
  `picassaPlantId` bigint(30) DEFAULT NULL,
  `picassaModified` datetime DEFAULT NULL,
  `gTileProcessed` tinyint(4) NOT NULL DEFAULT '0',
  `zoomEnabled` tinyint(4) NOT NULL DEFAULT '0',
  `processed` tinyint(4) NOT NULL DEFAULT '0',
  `boxFlag` tinyint(4) NOT NULL DEFAULT '0',
  `ocrFlag` tinyint(4) NOT NULL DEFAULT '0',
  `ocrValue` text,
  `nameFinderFlag` tinyint(4) NOT NULL DEFAULT '0',
  `nameFinderValue` text,
  `scientificName` varchar(30) DEFAULT NULL,
  `collectionCode` varchar(60) DEFAULT NULL,
  `catalogueNumber` int(11) DEFAULT NULL,
  `guessFlag` tinyint(4) NOT NULL DEFAULT '0',
  `tmpFamily` varchar(20) DEFAULT NULL,
  `tmpFamilyAccepted` varchar(20) DEFAULT NULL,
  `tmpGenus` varchar(20) DEFAULT NULL,
  `tmpGenusAccepted` varchar(20) DEFAULT NULL,
  `storageDeviceId` int(11) NOT NULL DEFAULT '1',
  `path` varchar(256) NOT NULL,
  `originalFileName` varchar(60) NOT NULL,
  `remoteAccessKey` varchar(100) NOT NULL DEFAULT '0',
  `statusType` tinyint(4) NOT NULL DEFAULT '0',
  `rating` float NOT NULL,
  PRIMARY KEY (`imageId`),
  KEY `family` (`family`),
  KEY `genus` (`genus`),
  KEY `scientificName` (`scientificName`),
  KEY `barcode` (`barcode`),
  KEY `catalogueNumber` (`catalogueNumber`),
  KEY `collectionCode` (`collectionCode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `imageAttrib`
--

CREATE TABLE IF NOT EXISTS `imageAttrib` (
  `imageId` int(11) NOT NULL DEFAULT '0',
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `attributeId` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `uIndex` (`imageId`,`categoryId`,`attributeId`),
  KEY `imageId` (`imageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Table structure for table `imageAttribType`
--

CREATE TABLE IF NOT EXISTS `imageAttribType` (
  `categoryId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `elementSet` varchar(255) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`categoryId`),
  UNIQUE KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `imageAttribValue`
--

CREATE TABLE IF NOT EXISTS `imageAttribValue` (
  `attributeId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `categoryId` int(11) DEFAULT NULL,
  PRIMARY KEY (`attributeId`),
  UNIQUE KEY `attributeId` (`attributeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `imageLog`
--

CREATE TABLE IF NOT EXISTS `imageLog` (
  `imageLogId` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(11) DEFAULT NULL,
  `beforeDesc` varchar(255) DEFAULT NULL,
  `afterDesc` varchar(255) DEFAULT NULL,
  `query` varchar(255) DEFAULT NULL,
  `dateCreated` datetime DEFAULT NULL,
  `imageId` int(11) DEFAULT NULL,
  PRIMARY KEY (`imageLogId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `imageRating`
--

CREATE TABLE IF NOT EXISTS `imageRating` (
  `imageId` int(11) NOT NULL,
  `userId` int(11) NOT NULL DEFAULT '0',
  `ipAddress` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT '0',
  `calc` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `uKey` (`imageId`,`userId`,`ipAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(20) NOT NULL,
  `table` varchar(40) NOT NULL,
  `query` text NOT NULL,
  `lastModifiedBy` int(11) NOT NULL,
  `modifiedTime` datetime NOT NULL,
  PRIMARY KEY (`logId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `masterLog`
--

CREATE TABLE IF NOT EXISTS `masterLog` (
  `masterLogId` int(11) NOT NULL AUTO_INCREMENT,
  `scId` varchar(20) NOT NULL,
  `logId` int(11) NOT NULL,
  `stationId` int(11) NOT NULL,
  `imageId` int(11) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `before` blob,
  `after` blob,
  `task` varchar(40) NOT NULL,
  `timestampModified` datetime DEFAULT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`scId`,`logId`,`stationId`),
  UNIQUE KEY `masterLogId` (`masterLogId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `processQueue`
--

CREATE TABLE IF NOT EXISTS `processQueue` (
  `imageId` varchar(40) NOT NULL DEFAULT '',
  `processType` varchar(20) NOT NULL,
  `extra` text,
  `dateAdded` datetime DEFAULT NULL,
  `processed` datetime DEFAULT NULL,
  `errors` tinyint(4) DEFAULT '0',
  `errorDetails` blob,
  PRIMARY KEY (`imageId`,`processType`),
  KEY `barcode` (`imageId`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `remoteAccess`
--

CREATE TABLE IF NOT EXISTS `remoteAccess` (
  `remoteAccessId` int(11) NOT NULL AUTO_INCREMENT,
  `ip` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `active` varchar(10) NOT NULL DEFAULT 'true',
  PRIMARY KEY (`remoteAccessId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `set`
--

CREATE TABLE IF NOT EXISTS `set` (
  `setId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`setId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `setValues`
--

CREATE TABLE IF NOT EXISTS `setValues` (
  `setValueId` int(11) NOT NULL AUTO_INCREMENT,
  `setId` int(11) NOT NULL,
  `attributeId` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`setValueId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `specimen2Label`
--

CREATE TABLE IF NOT EXISTS `specimen2Label` (
  `labelId` int(11) NOT NULL,
  `evernoteAccountId` int(11) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `dateAdded` datetime NOT NULL,
  UNIQUE KEY `labelId` (`labelId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storageDevice`
--

CREATE TABLE IF NOT EXISTS `storageDevice` (
  `storageDeviceId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `type` varchar(10) NOT NULL,
  `baseUrl` varchar(100) NOT NULL,
  `basePath` varchar(100) NOT NULL,
  `userName` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `key` varchar(50) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `defaultStorage` int(11) DEFAULT '0',
  `extra2` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`storageDeviceId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(25) NOT NULL DEFAULT '',
  `pw` varchar(32) NOT NULL DEFAULT '',
  `realName` varchar(32) NOT NULL DEFAULT '',
  `extraInfo` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `tmpMail` varchar(50) NOT NULL DEFAULT '',
  `accessLevel` tinyint(4) NOT NULL DEFAULT '0',
  `active` enum('y','n') NOT NULL DEFAULT 'n',
  `statusType` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `user` (`login`),
  UNIQUE KEY `mail` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `userPermissions`
--

CREATE TABLE IF NOT EXISTS `userPermissions` (
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

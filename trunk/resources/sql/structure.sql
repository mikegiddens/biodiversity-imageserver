SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `collection` (
  `collection_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `collectionSize` int(11) NOT NULL,
  PRIMARY KEY  (`collection_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `image_id` int(11) NOT NULL auto_increment,
  `filename` varchar(60) NOT NULL,
  `timestamp_modified` datetime default NULL,
  `barcode` varchar(20) default NULL,
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `Family` varchar(20) default NULL,
  `Genus` varchar(20) default NULL,
  `SpecificEpithet` varchar(20) default NULL,
  `flickr_PlantID` bigint(20) default NULL,
  `flickr_modified` datetime default NULL,
  `flickr_details` varchar(60) default NULL,
  `picassa_PlantID` bigint(30) default NULL,
  `picassa_modified` datetime default NULL,
  `gTileProcessed` tinyint(4) NOT NULL default '0',
  `zoomEnabled` tinyint(4) NOT NULL default '0',
  `processed` tinyint(4) NOT NULL default '0',
  `ocr_flag` tinyint(4) NOT NULL default '0',
  `ocr_value` text NOT NULL,
  `namefinder_flag` tinyint(4) NOT NULL default '0',
  `namefinder_value` text NOT NULL,
  `ScientificName` varchar(30) NOT NULL,
  `CollectionCode` varchar(10) NOT NULL,
  PRIMARY KEY  (`image_id`),
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
  `id` int(11) NOT NULL auto_increment,
  `sc_id` varchar(20) NOT NULL,
  `log_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `barcode` varchar(20) NOT NULL,
  `before` blob,
  `after` blob,
  `task` varchar(40) NOT NULL,
  `timestamp_modified` datetime default NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY  (`sc_id`,`log_id`,`station_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `process_queue`
--

CREATE TABLE IF NOT EXISTS `process_queue` (
  `image_id` varchar(40) NOT NULL default '',
  `process_type` varchar(20) NOT NULL,
  `extra` text,
  `date_added` datetime default NULL,
  `processed` datetime default NULL,
  `errors` tinyint(4) default '0',
  `error_details` blob,
  PRIMARY KEY  (`image_id`,`process_type`),
  KEY `Barcode` (`image_id`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(25) NOT NULL default '',
  `pw` varchar(32) NOT NULL default '',
  `real_name` varchar(32) NOT NULL default '',
  `extra_info` varchar(100) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `tmp_mail` varchar(50) NOT NULL default '',
  `access_level` tinyint(4) NOT NULL default '0',
  `active` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`login`),
  UNIQUE KEY `mail` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
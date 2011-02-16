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

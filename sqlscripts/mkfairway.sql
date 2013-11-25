-- Fairway Cabins Home Owners

DROP TABLE IF EXISTS `fairway`;
SET character_set_client = utf8;
CREATE TABLE `fairway` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `hphone` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


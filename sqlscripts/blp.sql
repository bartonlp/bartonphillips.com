-- BLP members 
-- Just keep track of ip addresses

DROP TABLE IF EXISTS `blpmembers`;
SET character_set_client = utf8;
CREATE TABLE `blpmembers` (
  `ip` varchar(255) NOT NULL,
  `count` int(11) default '0',
  `last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


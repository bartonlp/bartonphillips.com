DROP TABLE IF EXISTS `counter`;
CREATE TABLE IF NOT EXISTS `counter` (
  `filename` varchar(255) NOT NULL,
  `count` int(11),
  `last` timestamp,
  PRIMARY KEY  (`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

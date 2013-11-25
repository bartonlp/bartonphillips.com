drop table if exists logagent;

create table `logagent` (
  `ip` varchar(25) not null,
  `agent` varchar(255) not null,
  `count` int(11),
  `lasttime` timestamp,
  PRIMARY KEY (`ip`, `agent`)
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;


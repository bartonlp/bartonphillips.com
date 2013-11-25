CREATE TABLE `page` (
  `pageid` int(11) NOT NULL auto_increment,
  `page` varchar(255) NOT NULL,
  `query` varchar(255),
  `count` int(11) default '1',
  `lasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`pageid`),
  unique key (`page`, `query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ip` (
  `ipid` int(11) NOT NULL auto_increment,
  `pageid` int(11) NOT NULL default '0',
  `ip` varchar(20) NOT NULL default '',
  `ipcount` int(11) default '1',
  `iplasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ip`,`pageid`),
  KEY `ipid` (`ipid`),
  KEY `pageid` (`pageid`),
  CONSTRAINT `ip_ibfk_1` FOREIGN KEY (`pageid`) REFERENCES `page` (`pageid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `agent` (
  `agentid` int(11) NOT NULL auto_increment,
  `ipid` int(11) NOT NULL default '0',
  `pageid` int(11) NOT NULL default '0',
  `agent` varchar(255) NOT NULL default '',
  `agentcount` int(11) default '1',
  `agentlasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ipid`,`pageid`,`agent`),
  KEY `agentid` (`agentid`),
  KEY `pageid` (`pageid`),
  CONSTRAINT `agent_ibfk_1` FOREIGN KEY (`pageid`) REFERENCES `page` (`pageid`) ON DELETE CASCADE,
  CONSTRAINT `agent_ibfk_2` FOREIGN KEY (`ipid`) REFERENCES `ip` (`ipid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

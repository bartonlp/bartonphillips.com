-- BLP members 
-- Just keep track of ip addresses

DROP TABLE IF EXISTS wilderness;
SET character_set_client = utf8;
CREATE TABLE wilderness (
  id int(11) not null auto_increment,
  fname varchar(255),
  lname varchar(255),
  address varchar(255),
  phone varchar(30),
  phone2 varchar(30),
  email varchar(255),
  lasttime timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


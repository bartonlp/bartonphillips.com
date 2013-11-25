-- Create the member counter for each page
-- This gets the number of times each member visits a page.
-- We will only count members, that is id != 0

drop table if exists memberpagecnt;

create table `memberpagecnt` (
  `page` varchar(255) not null,
  `ip` varchar(25) not null,
  `agent` varchar(255) not null,
  `count` int(11),
  `lasttime` timestamp,
  PRIMARY KEY (`page`, `ip`, `agent`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1;

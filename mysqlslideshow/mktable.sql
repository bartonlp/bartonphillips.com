--
-- Main slide show table
--

CREATE TABLE `mysqlslideshow` (
 `id` int(11) NOT NULL auto_increment,
 `type` enum('link', 'data') default 'link', /* if link then data is a link address, if data then image. */
 `imageinfo` varchar(255) NOT NULL, /* return from getimagesize(img, extrinfo) serialized */
 `subject` varchar(255) default NULL,
 `description` text,
 `data` blob,
 `created` datetime,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

<?php
// BLP 2014-09-14 -- The .htaccess file has: ReWriteRule ^robots.txt$ robots.php [L,NC]
// This file reads the rotbots.txt file and outputs it and then gets the user agent string and
// saves it in the bots2 table.

require_once("/var/www/includes/siteautoload.class.php");
$S = new Database($dbinfo);

$robots = file_get_contents("robots.txt");
echo $robots;

$ip = $_SERVER['REMOTE_ADDR'];
$agent = $_SERVER['HTTP_USER_AGENT'];
/*
 CREATE TABLE `bots2` (
  `agent` varchar(255) NOT NULL,
  PRIMARY KEY (`agent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
*/
$S->query("insert ignore into barton.bots (ip) values('$ip')");
$S->query("insert ignore into barton.bots2 (agent) values('$agent')");

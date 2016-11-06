#!/usr/bin/php -q
<?php
// Cron job
// Get the ip address of bartonphillips.dyndns.org our dynamic DNS address
// insert it into the blpip table. If it already exists say No Change!
$_site = require_once(getenv("SITELOAD")."/siteload.php");
   
$D = new Database($_site->dbinfo);
$myip =  gethostbyname("bartonphillips.dyndns.org"); // get my home ip address

if(!$D->query("select myip from barton.myip where myip='$myip'")) {
  echo "NEW 'blpip': $myip\n";
  echo "*************************************************************\n";
  echo "******* IP For bartonphillips.dyndns.org HAS CHANGED ********\n";
  echo "*************************************************************\n";
}


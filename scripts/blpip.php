#!/usr/bin/php -q
<?php
   // Cron job
   // Get the ip address of bartonphillips.dyndns.org our dynamic DNS address
   // insert it into the blpip table. If it already exists say No Change!
require_once("/var/www/includes/siteautoload.class.php");
   
$D = new Database($dbinfo);
$blpip =  gethostbyname("bartonphillips.dyndns.org"); // get my home ip address
echo "blpip=$blpip\n";
try {
  $D->query("insert into blpip (blpIp, createtime) values ('$blpip', now())");
  echo "******* IP For bartonphillips.dyndns.org HAS CHANGED ********\n";
} catch(SqlException $e) {
  echo "No Change\n";
}

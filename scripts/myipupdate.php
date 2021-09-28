#!/usr/bin/php
<?php
$_site = require_once("/var/www/vendor/bartonlp/site-class/includes/siteload.php");
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$home = gethostbyname("bartonphillips.dyndns.org");
echo "home: $home\n";
$sql = "delete from $S->masterdb.myip where lasttime < current_date() - interval 1 day and myIp != '$home'";
$n = $S->query($sql);
echo "Done. $n deleted\n";


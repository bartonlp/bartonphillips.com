#!/usr/bin/php
<?php
// Delete entries from the myIp table older than three days that are not my home IP
// BLP 2021-10-26 -- change interval from 1 to 3 days
// BLP 2021-10-01 -- I now set SITELOADNAME in ~/.profile

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new Database($_site);

$home = gethostbyname("bartonphillips.dyndns.org");
echo "home: $home\n";
$sql = "delete from $S->masterdb.myip where lasttime < current_date() - interval 3 day and myIp != '$home'";
$n = $S->query($sql);
echo "Done. $n deleted\n";


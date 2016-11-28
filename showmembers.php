<?php
// Main page for bartonphillips.com
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$S->query("select * from members");
while($row = $S->fetchrow('assoc')) {
  extract($row);

  $info .= "$id, $name, $email, $ip, $agent, $created, $lasttime<br>";
}

echo $info;

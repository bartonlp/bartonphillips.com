<?php
// Reads the bartonphillips 'members' table

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->query("select * from members");
while($row = $S->fetchrow('assoc')) {
  extract($row);

  $info .= "$id, $name, $email, $ip, $agent, $created, $lasttime<br>";
}

echo $info;

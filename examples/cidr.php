<?php
// Test the CIDR.php class

require_once("CIDR.php");

echo "<h1>Demo of the CIDR class</h1>";
echo "<h2>DUBUG off then on.</h2>";
for($i=0; $i<2; ++$i) {
  CIDR::$DEBUG = $i;
  echo "DEBUG is ". ($i ? "ON" : "OFF"). "<br>";
  $ip = "2604:a880:1:20::5f4:1001";
  $cidr = "2604:A880::/32";

  $what = CIDR::match($ip, $cidr);
  echo "$ip is part of $cidr: " .($what ? 'true<br>' : 'false<br>');
  $ip = "45.55.27.116";
  $cidr = "45.55.0.0/16";
  $what = CIDR::match($ip, $cidr);
  echo "$ip is part of $cidr: " .($what ? 'true<br>' : 'false<br>');
  $ip = "45.56.27.116";
  $cidr = "45.56.0.0/18";
  $what = CIDR::match($ip, $cidr);
  echo "$ip is part of $cidr: " .($what ? 'true<br>' : 'false<br>');
  echo "---------------------------------------<br>";
}
<?php
// Test the CIDR.php class
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
ErrorClass::setDevelopment(true);

require_once("CIDR.php");

CIDR::$DEBUG = true;

$ip = "2604:a880:1:20::5f4:1001";
$cidr = "2604:A880::/32";

$what = CIDR::match($ip, $cidr);
echo "$ip is part of $cidr: " .($what ? 'true<br>' : 'false<br>');
echo "<br>";
$ip = "45.55.27.116";
$cidr = "45.55.0.0/16";
$what = CIDR::match($ip, $cidr);
echo "$ip is part of $cidr: " .($what ? 'true<br>' : 'false<br>');

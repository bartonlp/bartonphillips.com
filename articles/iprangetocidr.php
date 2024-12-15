<?php

$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$S->title ="Get CIDR from range";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

if($_POST['page'] == "find") {
  extract($_POST); // $start, $end, $page

  // Convert IP addresses to long integers
  $startInt = ip2long($start);
  $endInt = ip2long($end);

  // Calculate the number of usable IPs in the range (inclusive)
  // Subtract 1 to exclude the network address and broadcast address
  $numIPs = $endInt - $startInt + 1 - 2;

  // Get the number of bits required to represent the number of IPs
  $bitsRequired = 0;
  while ($numIPs > 0) {
    $bitsRequired++;
    $numIPs = $numIPs >> 1;
  }

  // Find the CIDR prefix based on number of bits required
  $cidrPrefix = 32 - $bitsRequired;

  // Reconstruct the CIDR notation in string format
  $cidr = long2ip($startInt) . "/$cidrPrefix";

  echo <<<EOF
$top
<hr>
<p>$start to $end is art of this cider: $cidr</p>
<hr>
$footer
EOF;
  exit();
}

echo <<<EOF
$top
<hr>
<form method="post">
<table>
<tr><td>Start IP Address</td><td><input type="text" name="start"></td></tr>
<tr><td>End IP Address</td><td><input type="text" name="end"></td></tr>
</table>
<button type="submit" name="page" value="find">Submit</button>
</form>
<hr>
$footer
EOF;



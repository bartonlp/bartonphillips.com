<?php
// Test the CIDR.php class

$_site = require_once getenv("SITELOADNAME");

require_once("CIDR.php");

$S = new SiteClass($_site);

$S->title = "IP to CIDR";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

if($_POST['page'] == "find") {
  extract($_POST); // $ip, $cidr, $page
  
  CIDR::$DEBUG = 0;
  $what = CIDR::match($ip, $cidr) ? "true" : "false";
  
  echo <<<EOF
$top
<hr>
<p>$ip is a part of $cidr: $what</p>
<hr>
$footer
EOF;
  exit();
}

echo <<<EOF
$top
<form method="post">
<table>
<tr><td>IP Address</td><td><input type="text" name="ip"></td></tr>
<tr><td>CIDR</td><td><input type="text" name="cidr"></td></tr>
</table>
<button type="submit" name="page" value="find">Submit</button>
</form>
<hr>
$footer
EOF;

  

       
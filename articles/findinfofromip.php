<?php
// This uses BigDataCloud. I got the file countries-codes.json from them.
// https://www.bigdatacloud.com/account. The account name and password are in Dashlane.
// Go to the account and click on 'Credentials' for the API key.
// My account ID is: a0fc067d-697f-4099-860e-5ef96c95e857

$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

if($_POST['page'] == "find") {
  $S->title = "IP Info";
  $S->banner = "<h1>$S->title</h1>";
  $S->css =<<<EOF
#results td { padding: 5px; }
EOF;

  // BLP 2024-07-17 - add more info
  
  $key = require '/var/www/PASSWORDS/Ip2Location-key';
  $bigdatakey = require '/var/www/PASSWORDS/BigDataCloudAPI-key';
  
  $ip = $_POST['ip'];
  
  if(($json = file_get_contents("https://api.ip2location.io/?key=$key&ip=$ip")) === false) exit("<h1>Not a Valid IP</h1><p>ip2location failed</p>");
  $info = json_decode($json);

  $proxy = $info->is_proxy ? "true" : "false";

  $ip2info = <<<EOF
<table id="results" border='1'>
<tr><td>IP Address</td><td>$ip</td></tr>
<tr><td>Country Code</td><td>$info->country_code</td></tr>
<tr><td>Country Name</td><td>$info->country_name</td></tr>
<tr><td>Reagion</td><td>$info->region_name</td></tr>
<tr><td>City</td><td>$info->city_name</td></tr>
<tr><td>Zip Code</td><td>$info->zip_code</td></tr>
<tr><td>Time Zone</td><td>$info->time_zone</td></tr>
<tr><td>Autonomous System Number</td><td>AS$info->asn</td></tr>
<tr><td>Autonomous System</td><td>$info->as</td></tr>
<tr><td>Proxy</td><td>$proxy</td></tr>
EOF;
  
  if(($json = file_get_contents("https://api-bdc.net/data/hazard-report?ip=$ip&key=$bigdatakey")) === false) exit("tor failed");
  $istor = json_decode($json);
  $tordisp = null;
  foreach($istor as $k=>$v) {
    if($v) {
      $tordisp .= "<tr><td>$k</td><td>$v</td></tr>";
    }
  }

  if(!empty($tordisp)) {
    $ip2info .= $tordisp;
  }
              
  if(($json = file_get_contents("https://api-bdc.net/data/user-risk?ip=$ip&key=$bigdatakey")) === false) exit("tor failed");
  $istor = json_decode($json);
  $ip2info .= "<tr><td>RiskLevel</td><td>$istor->description</td></tr></table>";
  
  [$top, $footer] = $S->getPageTopBottom();
  echo <<<EOF
$top
<hr>
$ip2info
<hr>
$footer
EOF;
  exit();
}

$S->title = "Find Info From IP";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<form method="post">
Input the IP Address: <input type="text" name="ip" autofocus><br>
<input type="hidden" name="page" value="find">
<button type="submit">Submit</button>
</form>
<hr>
$footer
EOF;
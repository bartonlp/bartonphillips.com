<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
/*
function checkUser($S) {
  // I could also look at the fingerprint for my know devices.
  // There are two file at /var/www/bartonphillipsnet/
  // 1) a json file
  // 2) a php file.
  // Each has the same information: a fingerprint and a label for the device.
  // Right now the following is a simpler way to do this.
  
  if($userEmail = explode(":", $_COOKIE['SiteId'])[2]) {
    $sql = "select email from members where email='$userEmail'";

    if(!$S->sql($sql)) {
      echo "<h1>Go Away</h1>";
      exit();
    } else {
      if($S->fetchrow('num')[0] != 'bartonphillips@gmail.com') {
        echo "<h1>Go Away</h1>";
        exit();
      }
    }
  } else {
    echo "<h1>Go Away</h1>";
    exit();
  }
};

checkUser($S);
*/

date_default_timezone_set("America/New_York");

$prefix = "https://cloud.iexapis.com/stable";

//BLP 2022-01-21 -- Get iex-token form my secure location

$token = require_once("/var/www/PASSWORDS/iex-token");

$sql = "select stock, price, qty, status from stocks where status = 'active'";
$S->sql($sql);

$ar = [];
$stocks = "";

while([$stock, $price, $qty, $status] = $S->fetchrow('num')) {
  $stocks .= "$stock,";
  $ar[$stock] = ['price'=>$price, 'qty'=>$qty, 'status'=>$status];
}

$totalyield = 0;
$total = 0;
$totalcnt = 0;

$str = "$prefix/stock/market/batch?symbols=$stocks&types=quote&filter=latestPrice&token=$token";

$ret = json_decode(file_get_contents($str));
//varexport("ret", $ret);
//exit();
foreach($ret as $k=>$v) {
  //vardump("v", $v->quote);
  //$x = $v->quote;
  echo "$k: " .number_format($v->quote->latestPrice, 2) . "<br>";
}

  
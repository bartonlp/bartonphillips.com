<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new Database($_site);

function checkUser($S) {
  if($userId = $_COOKIE['SiteId']) {
    $sql = "select name from members where id=$userId";

    if($n = $S->query($sql)) {
      list($memberName) = $S->fetchrow('num');
      if($memberName != "Barton Phillips") {
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

$alphakey = "FLT73FUPI9QZ512V";

$S->query("select stock from stocks.stocks where stock in('RDS-A')");
while(list($stock) = $S->fetchrow('num')) {
  //$stock = preg_replace(["/-BLP/", "/RDS-A/"], ['', 'RDS.A'], $stock);
  
  $str = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";

  $h = curl_init();
  curl_setopt($h, CURLOPT_URL, $str);
  curl_setopt($h, CURLOPT_HEADER, 0);
  curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

  $alpha = curl_exec($h);
  $alpha = json_decode($alpha, true); // decode as an array

  $ar = $alpha["Time Series (Daily)"];
  
  foreach($ar as $k=>$v) {
    $date = $k;
    $volume = $v["5. volume"];
    echo "$stock - $date: $volume<br>";
    
    $sql = "update stocks.pricedata set volume='$volume' where date='$date' and stock='$stock'";
    $S->query($sql);
  }
}

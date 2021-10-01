<?php
// stockinit.php
// This is used only to initialize the empty table. NOT USED otherwise.
// Initialize the 'pricedata' table with the last 100 closing prices.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

function checkUser($S) {
  //echo "cookie: ". $_COOKIE['SiteId']."<br>";
  
  if($userEmail = explode(":", $_COOKIE['SiteId'])[1]) {
    $sql = "select name from members where email='$userEmail'";

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

// alpha vantage api key: FLT73FUPI9QZ512V
$alphakey = "FLT73FUPI9QZ512V";

$sql = "select stock from stocks.stocks";
$S->query($sql);
while(list($stock) = $S->fetchrow('num')) {
  $alp = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";
  $h = curl_init();
  curl_setopt($h, CURLOPT_URL, $alp);
  curl_setopt($h, CURLOPT_HEADER, 0);
  curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

  $alpha = curl_exec($h);
  $alpha = json_decode($alpha, true); // decode as an array

  $ar = $alpha["Time Series (Daily)"];

  foreach($ar as $k=>$v) {
    $date = $k;
    $price = $v["4. close"];
    echo "$stock: $date: $price<br>";
    $sql = "insert into stocks.pricedata (stock, date, price) values('$stock', '$date', '$price') ".
           "on duplicate key update date='$date', price='$price'";
    $S->query($sql);
  }
}
echo "DONE<br>";
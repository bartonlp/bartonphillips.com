#! /usr/bin/php
<?php
// BLP 2018-02-07 -- Added 'volume' to table.  
// do an update via CRON of the pricedata table.
$_site = require_once("/var/www/vendor/bartonlp/site-class/includes/siteload.php");
ErrorClass::setDevelopment(true);
$S = new Database($_site);

$sql = "select stock, price, qty from stocks.stocks where status='active'";
$S->query($sql);

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  // NOTE Alpha needs RDS-A while iex wants RDS.A
  $stock = ($stock == "RDS-A") ? "RDS.A" : $stock; 
  
  $alphakey = "FLT73FUPI9QZ512V";
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
    $price = $v["4. close"];
    $volume = $v["5. volume"];
    //echo "$stock: $date: $price<br>";
    $sql = "insert ignore into stocks.pricedata (stock, date, price, volume) ".
           "values('$stock', '$date', '$price', '$volume')";
           $S->query($sql);
    echo "$stock, $date, $price, $volume\n";
  }
  readline("Next:");
}

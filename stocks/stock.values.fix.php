<?php
// stock.values.fix.php
// This does the initial filling of the `values` table. Once it is done this is NOT used any more.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$alphakey = "FLT73FUPI9QZ512V";

$S->query("select stock, qty from stocks.stocks where status='active'");
$r = $S->getResult();

while(list($stock, $qty) = $S->fetchrow($r, 'num')) {
  $str = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $alpha = curl_exec($ch);
  $alpha = json_decode($alpha, true); // decode as an array

  $ar = $alpha["Time Series (Daily)"];

  foreach($ar as $k=>$v) {
    $date = $k;
    $price = $v["4. close"];
    $value = $price * $qty;

    if($stock == "RDS-A") $stock = "RDS.A";
    
    $sql = "insert into stocks.`values` (stock, date, value) ".
           "values('$stock', '$date', '$value') ".
           "on duplicate key update value='$value'";

    $S->query($sql);
  }
}

echo "DONE<br>";



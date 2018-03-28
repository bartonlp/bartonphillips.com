<?php
// stockquotealpha.php
// Uses Alpha, and IEX
// Get info for a single stock.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

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

//https://www.alphavantage.co/query?function=BATCH_STOCK_QUOTES&symbols=MSFT,FB,AAPL&apikey=demo

$sql = "select stock, price, qty from stocks.stocks where status not in('watch','sold')";
$S->query($sql);

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  // NOTE Alpha needs RDS-A while iex wants RDS.A

  $stocks[$stock] = [$price, $qty];

  $str = "https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY_ADJUSTED&symbol=".
         $stock. "&apikey=". $alphakey;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // We get the stocks from the array above  
  $alpha = curl_exec($ch);

  $alpha = json_decode($alpha, true); // decode as an array
  //vardump("alpha", $alpha);
  
  $div = 0;
  
  $ar1 = array_values($alpha["Monthly Adjusted Time Series"]);
  for($i=0; $i<12; ++$i) {
    if($ar1[$i]["7. dividend amount"] == 0) continue;
    $div = $ar1[$i]["7. dividend amount"];
    break;
  }
  $div *= 4;
  echo "$stock, div: $div<br>";
}

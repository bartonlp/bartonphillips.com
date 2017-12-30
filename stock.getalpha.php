<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

if($stock = $_POST['stock']) {
  $stock = strtoupper($stock);
  $alphakey = "FLT73FUPI9QZ512V";
  $str = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $alpha = curl_exec($ch);
  $alpha = json_decode($alpha, true); // decode as an array

  $alpha = print_r($alpha, true);

  $prefix = "https://api.iextrading.com/1.0";
  $str = "$prefix/stock/market/batch?symbols=$stock&types=quote";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // We get the stocks from the array above  
  $ret = curl_exec($ch);
  
  $ar = json_decode($ret);
  $iex = print_r($ar, true);

  $h->title = "Raw Data";
  $h->banner = "<h1>Raw Results From alpha and iex</h1>";
  $h->css =<<<EOF
  <style>
#alpha {
  font-size: .7rem;
  border: 1px solid black;
  width: 100%;
  height: 300px;
  padding: .5rem;
  overflow: auto;
}
#iex {
  font-size: .7rem;
  border: 1px solid black;
  width: 100%;
  height: 300px;
  padding: .5rem;
  overflow: auto;
}
  </style>
EOF;
  
  list($top, $footer) = $S->getPageTopBottom($h);

  echo <<<EOF
$top
<pre>
<h3>Alpha</h3>
<div id="alpha">
$alpha
</div>
<h3>Iex</h3>
<div id="iex">
$iex
</div>
</pre>
$footer
EOF;
  exit();
}

$h->title = "Raw Data";
$h->banner = "<h1>Raw Data From alpha and iex</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form method="post">
Enter Stock Symbol: <input type="text" name="stock" autofocus><br>
<input type="submit" value="Submit">
</form>
$footer
EOF;
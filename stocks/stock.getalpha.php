<?php
// stock.getalpha.php
// Get the info from Alphavantage and iex for one stock.

// https://www.alphavantage.co/documentation
// https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=MSFT&interval=1min&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=MSFT&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=MSFT&apikey=demo

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

if($stock = $_POST['stock']) {
  $stock = strtoupper($stock);
  // BLP 2022-01-21 -- Get the keys form my secure location
  
  $alphakey = require_once("/var/www/bartonphillipsnet/PASSWORDS/alpha-token");
  // $iex_token = require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");
  $iex_token = file_get_contents("https://bartonphillips.net/PASSWORDS/iex-token.php");
  
  $str = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$stock&apikey=$alphakey";
  $alpha = json_decode(file_get_contents($str), true); // decode as an array

  $alpha = print_r($alpha, true);

  $str = "https://cloud.iexapis.com/stable/stock/$stock/batch?types=quote&token=$iex_token";

  $iex = print_r(json_decode(file_get_contents($str)), true);

  $str = "https://cloud.iexapis.com/stable/stock/$stock/stats?token=$iex_token";

  $iexstats = print_r(json_decode(file_get_contents($str), true), true);
  
  $h->title = "Raw Data";
  $h->banner = "<h1>Raw Results From alpha and iex</h1>";
  $h->css =<<<EOF
#alpha {
  font-size: .7rem;
  border: 1px solid black;
  width: 100%;
  padding: .5rem;
  overflow: auto;
}
#iexstats, #iexquote {
  font-size: .7rem;
  border: 1px solid black;
  width: 100%;
  padding: .5rem;
  overflow: auto;
}
EOF;
  
  list($top, $footer) = $S->getPageTopBottom($h);

  echo <<<EOF
$top
<pre>
<h3>Alpha</h3>
<div id="alpha">
$alpha
</div>
<h3>IEX STATS</h3>
<div id="iexstats">
$iexstats
</div>
<h3>IEX QUOTE</h3>
<div id="iexquote">
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

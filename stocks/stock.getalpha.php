<?php
// stock.getalpha.php
// Get the info from Alphavantage for one stock.

// https://www.alphavantage.co/documentation
// https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=MSFT&interval=1min&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=MSFT&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=MSFT&apikey=demo

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($stock = $_POST['stock']) {
  $stock = strtoupper($stock);
  // BLP 2022-01-21 -- Get the keys form my secure location
  
  $alphakey = require_once("/var/www/PASSWORDS/alpha-token");

  $str = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$stock&apikey=$alphakey";
  $alpha = json_decode(file_get_contents($str), true); // decode as an array

  $alpha = print_r($alpha, true);
  
  $S->title = "Raw Data";
  $S->banner = "<h1>Raw Results From alpha</h1>";
  $S->css =<<<EOF
#alpha {
  font-size: .7rem;
  border: 1px solid black;
  width: 100%;
  padding: .5rem;
  overflow: auto;
}
EOF;
  
  [$top, $footer] = $S->getPageTopBottom();

  echo <<<EOF
$top
<pre>
<h3>Alpha</h3>
<div id="alpha">
$alpha
</div>
</pre>
$footer
EOF;
  exit();
}

$S->title = "Raw Data";
$S->banner = "<h1>Raw Data From alpha</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<form method="post">
Enter Stock Symbol: <input type="text" name="stock" autofocus><br>
<input type="submit" value="Submit">
</form>
$footer
EOF;

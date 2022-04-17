<?php
// stock-price-update.php (Stock Quotes)
// uses stock-price-update.js
// BLP 2022-02-19 -- Add EndOfDay GET used by ../scripts/updatestocktotals.sh. This is run by CRON
// Mon-Fri.
// BLP 2022-02-01 -- Had to update PHPHtmlParser\Dom Node/Collections.php to add 'mixed' as a
// return type (or could have added #[\ReturnTypeWillChange] before the function offsetGet(). No
// actual change in this file. 
// BLP 2022-01-18 -- use $v foreach($iex as $k=>$v) and remove $a and $i. Add comments
// BLP 2021-11-04 -- Remove stock-price-update-worker.js. Fixed secret.
// BLP 2018-03-07 -- Uses Roboto from /var/www/bartonphillips.com/fonts
// BLP 2020-06-04 -- Use iex to get the avgTotalVolume and day200MovingAvg instead of reading the
// database.
// BLP 2020-09-17 -- Removed the WSJ logic for Damiler as it is available from iex
// BLP 2020-10-21 -- Include mutual funds in list returned by AJAX. Move qty and value together. In stock-price-update.js include mutual funds
// along with 'active'.

$_site = require_once(getenv("SITELOADNAME"));

use PHPHtmlParser\Dom;

function checkUser($S) {
  // I could also look at the fingerprint for my know devices.
  // There are two file at /var/www/bartonphillipsnet/
  // 1) a json file
  // 2) a php file.
  // Each has the same information: a fingerprint and a label for the device.
  // Right now the following is a simpler way to do this.
  
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

// AJAX GET. EndOfDay is run by CRON at 1700 via bartonphillips.com/scripts/updatestocktotals.sh
// which does a wget of bartonphillips.com/stocks/stock-price-update.php?page=EndOfDay
/*
CREATE TABLE `stocktotals` (
  `total` varchar(50) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`total`,`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

if($_GET['page'] == "EndOfDay") {
  $S = new Database($_site);
  
  $iex_token = require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");

  // Get our stocks
  
  $sql = "select stock, qty from stocks where status = 'active'";
  
  $S->query($sql);

  while([$stock, $qty] = $S->fetchrow('num')) {
    $ar[$stock] = $qty;
  }

  $symboleList = implode(",", array_keys($ar));

  $str = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=$symboleList".
         "&types=quote".
         "&token=$iex_token";

  $iex = json_decode(file_get_contents($str), true); // decode as an array

  $total = $stocktotal = $mutual = 0;
  $value = 0;
  
  foreach($iex as $k=>$v) {
    $value = $v['quote']['latestPrice'] * $ar[$k];
    echo "$k: $value, $ar[$k]\n";
    $stocktotal += $value;
  }

  // Now get the mutual funds via alpha

  $alphakey = require("/var/www/bartonphillipsnet/PASSWORDS/alpha-token");
  
  $sql = "select stock, qty from stocks where status = 'mutual'";
  
  $S->query($sql);

  while([$stock, $qty] = $S->fetchrow('num')) {
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$stock&apikey=$alphakey";

    $alpha = json_decode(file_get_contents($url), true)['Global Quote'];
  
    $mdate = $alpha['07. latest trading day'];
    $close = $alpha['05. price'];
    $value = $close * $qty;
    $mutual += $value;
    echo "$stock: $value, $qty -- $mdate\n";
  }
  $total = $stocktotal + $mutual;
  $total = number_format(round($total, 2), 2);
  $mutual = number_format(round($mutual, 2), 2);
  $stocktotal = number_format(round($stocktotal, 2), 2);
  $date = date("Y-m-d H:i:s");
  echo "\nMutuals $mdate: $mutual";
  echo "\nStocks $date: $stocktotal";
  echo "\nTotal $date: $total\n";

  $S->query("insert into stocktotals (total, created) values('$total', current_date()) " .
           "on duplicate key update total='$total'");
  exit();
}

// AJAX from stock-price-quote.js
// Get info from stocks table and DJIA (Dow Jones Industrial Average) from WSJ
// (quotes.wks.com/index.DJIA).
// This now uses IEX for stocks and Alpha mutual funds.

if($_POST['page'] == 'web') {
  $S = new Database($_site); // All we need here is the database.

  // Get token from secure location.
  // There are two ways to do this via ../PASSWORDS.
  // 1) require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");
  // 2) file_get_contents("https://bartonphillips.net/PASSWORDS/iex-token.php");
  // The second way reads a plain text via an echo.

  $iex_token = require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");
  
  $sql = "select stock, price, qty, status, name from stocks where status='active'";
  
  $S->query($sql);

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow('num')) {
    $ar[$stock] = ["price"=>$price, "qty"=>$qty, "status"=>$status, "company"=>$company];
  }

  $symboleList = implode(",", array_keys($ar));

  // Get both 'quote' and 'status' and filter for latestPrice, change, changePercent, latestUpdate,
  // avgTotalVolume and day200MovingAvg.
  
  $str = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=$symboleList".
         "&types=quote,stats&filter=latestPrice,change,changePercent,latestUpdate,".
         "avgTotalVolume,day200MovingAvg".
         "&token=$iex_token";

  $iex = json_decode(file_get_contents($str), true); // decode as an array

  foreach($iex as $k=>$v) {
    $key = &$ar[$k]; // make $key a reference to $ar[$k].
    $val = $v['quote']; // here we just need a seperate variable

    $key['moving'] = $v['stats']['day200MovingAvg'];
    
    $key['latestPrice'] = $val['latestPrice'];
    $key['change'] = $val['change'];
    $key['changePercent'] = $val['changePercent'];
    $key['latestUpdate'] = $val['latestUpdate'];
    $key['avgTotalVolume'] = $val['avgTotalVolume'];
  }

  // Dom lets one use the dom to scape the website.
  
  $dom = new Dom;

  $dom->loadFromUrl('https://www.marketwatch.com/investing/index/djia');

  $quoteDate = $dom->find(".timestamp__time bg-quote")->text();

  $group = $dom->find(".markets__group");

  $dji = $group->find(".price bg-quote")->text;
  $change = $group->find(".change bg-quote")->text;
  $changePercent = $group->find(".percent bg-quote")->text;

  // Get Mutuals from alphavanage.co because iex stopped giving me mutuals

  $sql = "select stock, qty from stocks where status = 'mutual'";
  
  $S->query($sql);

  $alphakey = require("/var/www/bartonphillipsnet/PASSWORDS/alpha-token");

  while([$mutual, $qty] = $S->fetchrow('num')) {
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$mutual&apikey=$alphakey";

    $alpha = json_decode(file_get_contents($url), true)['Global Quote'];

    $mdate = $alpha['07. latest trading day'];
    $close = $alpha['05. price'];
    $value = $close * $qty;
    $myret[$mutual] = [$close, $value, $qty, $mdate];
  }

  $ret = json_encode(array('stocks'=>$ar, // price, qty, status, company, moving, latestPrice, change, changePercent, latestUpdate and avgTotalVolume.
                           'mutuals'=>$myret, // close, value, qty, mdate (mutual date).
                           'dji'=>$dji, // Current Dow value
                           'change'=>$change, // Dow change during the day
                           'per'=>$changePercent, // Dow percent change
                           'date'=>$quoteDate // Dow quote date
                          )
                    );
  
  echo $ret;
  exit();
}

$S = new $_site->className($_site);

checkUser($S);

$h->title = "Updating Stock Quotes";

$h->css =<<<EOF
/* Use Roboto font from DocRoot/fonts */
@font-face {
  font-family: "Roboto";
  src: url("/fonts/Roboto/Roboto-Regular.ttf");
}
@font-face {
  font-family: "Roboto black";
  src: url("/fonts/Roboto/Roboto-Black.ttf");
}
@font-face {
  font-family: "Roboto black italic";
  src: url("/fonts/Roboto/Roboto-BlackItalic.ttf");
}
@font-face {
  font-family: "Roboto bold";
  src: url("/fonts/Roboto/Roboto-Bold.ttf");
}
/* use Roboto Regular in body */
body {
  font-family: "Roboto";
}
#stocks { width: 100%; }  
#stocks th {
  padding: .3rem;
}
#stocks td {
  padding: .3rem;
  text-align: right;
}
#stocks td:first-child {
  background-color: lightblue;
  color: black;
}
#stocks td:first-child {
  cursor: pointer;
}
/* The first td has stock and company each in a span. This is the second span which is company */
/* Use Roboto Bold for the company spans. */
#stocks td:first-child span:last-child {
  font-family: "Roboto bold";
  font-size: .5rem;
  line-height: 0;
  text-transform: capitalize;
}
select {
  font-size: 1rem;
}
/* Use Roboto Bold for stocks */
#stocks td:first-child {
  font-family: "Roboto bold";
  text-align: left;
  line-height: 60%;
}
/* Use Roboto Bold for 'Buy Price/% Diff' */
#stocks td:nth-child(4) {
  font-family: "Roboto bold";
}
#mutuals { width: 100%; }
#mutuals td { padding: .3rem; }
#mutuals td:nth-of-type(2), td:nth-of-type(3), td:nth-of-type(4), td:nth-of-type(5) { text-align: right; }
#totals { width: 500px; }
/* Use Roboto Bold for the 'totals' table right below 'stocks' table */
#totals th {
  font-family: "Roboto bold";
  text-align: left;
  width: 3rem;
}
#totals th:last-child {
  text-align: right;
}
#attribution { margin-top: 10px; }
/* A span to make negative values red */
.neg {
  color: red;
}
/* A span for the Dow Jones "As of" date */
.small {
  font-size: .8rem;
}
#loading {
  display: block;
  width: 80px;
  height: 80px;
  margin-left: 50%;
}
EOF;

$h->banner = "<h1>Stock Quotes</h1>";

// Put the js at the end just befor the closing </body>

$b->script = "<script src='stock-price-update.js'></script>";

[$top, $footer] = $S->getPageTopBottom($h, $b);

// Render page with the 'loading' icon. Once the worker get all of the data the
// stock-price-update.js will rerender the page with all of the data.
date_default_timezone_set('America/New_York');
$date = date("r T", time());

echo <<<EOF
$top
<hr>
<h4>Today is: $date</h4>            

<div id="selectstatus"></div>

<div>The <i>Av Price</i> is a moving average over the last 200 days. <i>Av Vol</i> is the average over last 30 days.</div>
<div id='dji'></div>
<div id='stock-data'><img id="loading" src="https://bartonphillips.net/images/loading.gif"</img></div>
<div id='attribution'></div>
<hr>
$footer
EOF;

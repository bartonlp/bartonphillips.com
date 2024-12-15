<?php
// Nasdac data link key: aRs3NWLdTHFWQAqqsnEj
// stock-price-update.php (Stock Quotes)
// uses stock-price-update.js
/*
CREATE TABLE `stocks` (
  `stock` varchar(10) NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','watch','sold','mutual','IRA') DEFAULT NULL,
  PRIMARY KEY (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/

//exit("<h1>cloud.ipxapis.com No Longer Has a Free Subscription</h1>");

$_site = require_once(getenv("SITELOADNAME"));

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

  $iex_token = require_once("/var/www/PASSWORDS/iex-token");

  // Get our stocks
  
  $sql = "select stock, qty from stocks where status = 'active'";
  
  $S->sql($sql);

  while([$stock, $qty] = $S->fetchrow('num')) {
    $ar[$stock] = $qty;
  }

  $symboleList = implode(",", array_keys($ar));

  $str = "https://api.iex.cloud/v1/data/core/iex_tops/$symboleList?token=$iex_token";

  $iex = json_decode(file_get_contents($str), true); // decode as an array

  $total = $stocktotal = $mutual = 0;
  $value = 0;

  for($i=0; $i < count($iex); ++$i) {
    $x = $iex[$i];
    $z = $x['symbol'];
    $price = $y[$z]['price'] = $x['lastSalePrice'];
    $y[$z]['time'] = $x['lastSaleTime'];
    $y[$z]['volume'] = $x['volume'];

    $S->sql("insert into stocksmoving values('$z', '$price', now())");
  }
  
  foreach($y as $k=>$v) {
    $value = $y[$k]['price'] * $ar[$k]; // price * qty
    echo "$k: price={$y[$k]['price']}, total price=$value, qty= $ar[$k]<br>";
    $stocktotal += $value;
  }

  // Now get the mutual funds via alpha

  $alphakey = require("/var/www/PASSWORDS/alpha-token");
  
  $sql = "select stock, qty from stocks where status = 'mutual'";
  
  $S->sql($sql);

  while([$stock, $qty] = $S->fetchrow('num')) {
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$stock&apikey=$alphakey";

    $alpha = json_decode(file_get_contents($url), true)['Global Quote'];
  
    $mdate = $alpha['07. latest trading day'];
    $close = $alpha['05. price'];
    $value = $close * $qty;
    $mutual += $value;
    echo "$stock: $value, $qty -- $mdate<br>";
  }
  $total = $stocktotal + $mutual;
  $total = number_format(round($total, 2), 2);
  $mutual = number_format(round($mutual, 2), 2);
  $stocktotal = number_format(round($stocktotal, 2), 2);
  $date = date("Y-m-d H:i:s");
  echo "\nMutuals $mdate: $mutual";
  echo "\nStocks $date: $stocktotal";
  echo "\nTotal $date: $total<br>";

  $S->sql("insert into stocktotals (total, created) values('$total', current_date()) " .
           "on duplicate key update total='$total'");
  exit();
}

// AJAX from stock-price-quote.js
// Get info from stocks table.
// This now uses IEX for stocks and Alpha mutual funds.

if($_POST['page'] == 'web') {
  $S = new Database($_site); // All we need here is the database.

  $alphakey = require("/var/www/PASSWORDS/alpha-token");
  $iex_token = require("/var/www/PASSWORDS/iex-token");

  $sql = "select stock, price, qty, status, name from stocks where status='active'";
  
  $S->sql($sql);

  while([$stock, $price, $qty, $status, $company] = $S->fetchrow('num')) {
    $ar[$stock] = ["price"=>$price, "qty"=>$qty, "status"=>$status, "company"=>$company];
    $stocks .= "$stock,";
  }

  $stocks = rtrim($stocks, ',');
  
  $str = "https://api.iex.cloud/v1/data/core/iex_tops/$stocks?token=$iex_token";
  $iex = json_decode(file_get_contents($str), true); // decode as an array

  for($i=0; $i < count($iex); ++$i) {
    $x = $iex[$i];
    $ar[$x['symbol']]['currPrice'] = $x['lastSalePrice'];
    $ar[$x['symbol']]['currTime'] = $x['lastSaleTime'];
    $ar[$x['symbol']]['volume'] = $x['volume'];
  }
  //error_log("stockprice.php ret: ".print_r($ar, true));
  $ret = json_encode($ar);
  echo $ret;
  exit();
}

$S = new $_site->className($_site);

//checkUser($S);

$S->title = "Updating Stock Quotes";

$S->css =<<<EOF
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
/*#stocks { width: 100%; }*/
#stocks th, #mutuals th {
  padding: .3rem;
}
#stocks td, #mutuals td {
  padding: .3rem;
  text-align: right;
}
#stocks td:first-child, #mutuals td:first-child {
  background-color: lightblue;
  color: black;
  text-align: left;
  width: 13rem;
  font-family: "Roboto bold";
  line-height: 60%;
  cursor: pointer;
}
#stocks td:nth-of-type(2), #mutuals td:nth-of-type(2) {
  width: 6.5rem;
}
/* The first td has stock and company each in a span. This is the second span which is company */
/* Use Roboto Bold for the company spans. */
#stocks td:first-child span:last-child, #mutuals td:first-child span:last-child {
  font-family: "Roboto bold";
  font-size: .5rem;
  line-height: 0;
  text-transform: capitalize;
}
select {
  font-size: 1rem;
}
/* Use Roboto Bold for 'Buy Price/% Diff' */
#stocks td:nth-child(4), #mutuals td:nth-child(4) {
  font-family: "Roboto bold";
}
/* make stocks and mutuals line up on the fist five */
#stocks td:nth-of-type(3), #stocks td:nth-of-type(4), #stocks td:nth-of-type(5),
#mutuals td:nth-of-type(3), #mutuals td:nth-of-type(4), #mutuals td:nth-of-type(5) {
  width: 6.5rem;
}
/*#mutuals { width: 100%; }*/
#mutuals td:nth-of-type(2), td:nth-of-type(3), td:nth-of-type(4), td:nth-of-type(5) { text-align: right; }
/*#totals { width: 500px; }*/
/* Use Roboto Bold for the 'totals' table right below 'stocks' table */
#totals th {
  font-family: "Roboto bold";
  text-align: left;
  width: 13rem;
  padding: 0 .3rem 0 .3rem;
}
#totals th:last-child {
  text-align: right;
  width: 6.5rem;
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

$S->banner = "<h1>Stock Quotes</h1>";

// Put the js at the end just befor the closing </body>

$S->b_script = "<script src='stockprice.js'></script>";

[$top, $footer] = $S->getPageTopBottom();

// Render page with the 'loading' icon. Once the worker get all of the data the
// stock-price-update.js will rerender the page with all of the data.
date_default_timezone_set('America/New_York');
$date = date("r T", time());

echo <<<EOF
$top
<hr>
<h4>Today is: $date</h4>            

<div id="selectstatus"></div>

<!--<div>The <i>Av Price</i> is a moving average over the last 200 days. <i>Av Vol</i> is the average over last 30 days.</div>-->
<div id='stock-data'><img id="loading" src="https://bartonphillips.net/images/loading.gif"</img></div>
<div id='attribution'></div>
<hr>
$footer
EOF;

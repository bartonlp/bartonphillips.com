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


$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

require './StockApi.php';

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

  $stock = new StocksApi($S);

  $data = $stock->getQuotes();

  $dji = $data->DIA->quote->pc * 100;
  $total = $data->grandTotal;

  $sql = "insert into stocktotals (dji, total, created) values('$dji', '$total', current_date())";
  echo "$sql<br>";
         
  // This is in the bartonphillips database which is what I am using.
  
//  $S->sql("insert into stocktotals (dji, total, created) values('$dji', '$total', current_date()) " .
//           "on duplicate key update dji='$dji', total='$total'")

  exit();
}

// AJAX from stock-price-quote.js
// Get info from stocks table.

if($_POST['page'] == 'web') {
  $S = new Database($_site); // All we need here is the database.

  $stock = new StocksApi($S);
  $data = $stock->getQuotes(); // 'active' is the default
  
  $ret = json_encode($data);
  header('Content-Type: application/json');
  echo $ret;
  exit();
}

$S = new SiteClass($_site);

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

$S->b_script = "<script src='/dist/stockprice.js'></script>";

[$top, $footer] = $S->getPageTopBottom();

// Render page with the 'loading' icon. Once the worker get all of the data the
// stockprice.js will rerender the page with all of the data.
date_default_timezone_set('America/New_York');
$date = date("r T", time());

echo <<<EOF
$top
<hr>
<h4>Today is: $date</h4>
<h3>Dow Jones Industrial Average is <span id="DJI"></span></h3>

<div id="selectstatus"></div>

<!--<div>The <i>Av Price</i> is a moving average over the last 200 days. <i>Av Vol</i> is the average over last 30 days.</div>-->
<div id='stock-data'><img id="loading" src="https://bartonphillips.net/images/loading.gif"</img></div>
<div id='attribution'></div>
<hr>
$footer
EOF;

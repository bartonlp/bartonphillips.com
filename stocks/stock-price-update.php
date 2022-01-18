<?php
// stock-price-update.php (Stock Quotes)
// uses stock-price-update.js
// BLP 2022-01-18 -- use $v foreach($iex as $k=>$v) and remove $a and $i. Add comments
// BLP 2021-11-04 -- Remove stock-price-update-worker.js. Fixed secret.
// BLP 2018-03-07 -- Uses Roboto from /var/www/bartonphillips.com/fonts
// BLP 2020-06-04 -- Use iex to get the avgTotalVolume and day200MovingAvg instead of reading the
// database.
// BLP 2020-09-17 -- Removed the WSJ logic for Damiler as it is available from iex
// BLP 2020-10-21 -- Include mutual funds in list returned by AJAX. Move qty and value together. In stock-price-update.js include mutual funds
// along with 'active'.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

use PHPHtmlParser\Dom;

function checkUser($S) {
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

// AJAX
// Get info from stocks table and DJIA (Dow Jones Industrial Average) from WSJ
// (quotes.wks.com/index.DJIA).

if($_POST['page'] == 'web') {
  $S = new Database($_site); // All we need here is the database.

  // BLP 2021-11-03 -- get token from secure location
  
  $iex_token = require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");
  //error_log("stock-price-update.php AJAX: iex_token=$iex_token");
  
  // BLP 2020-10-21 -- include mutual funds
  
  $sql = "select stock, price, qty, status, name from stocks";
  //"where status != 'mutual'";
  
  $S->query($sql);

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow('num')) {
    if($stock == "RDS-A") {
      $stock = "RDS.A";
    }
    $ar[$stock] = ["price"=>$price, "qty"=>$qty, "status"=>$status, "company"=>$company];
  }

  $symboleList = implode(",", array_keys($ar));

  //error_log("stock-price-update.php AJAX: symboleList=$symboleList");

  $str = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=$symboleList".
         "&types=quote,stats&filter=latestPrice,change,changePercent,latestUpdate,".
         "avgTotalVolume,day200MovingAvg".
         "&token=$iex_token";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $iex = curl_exec($ch);
  //error_log("stock-price-update.php AJAX: iex string=$iex");
  
  $iex = json_decode($iex, true); // decode as an array

  foreach($iex as $k=>$v) {
    $ar[$k]['moving'] = $v['stats']['day200MovingAvg'];
    
    $ar[$k]['latestPrice'] = $v['quote']['latestPrice'];
    $ar[$k]['change'] = $v['quote']['change'];
    $ar[$k]['changePercent'] = $v['quote']['changePercent'];
    $ar[$k]['latestUpdate'] = $v['quote']['latestUpdate'];
    $ar[$k]['avgTotalVolume'] = $v['quote']['avgTotalVolume'];
  }
   
  // Dom lets one use the dom to scape the website.
  
  $dom = new Dom;

  $dom->loadFromUrl('https://www.marketwatch.com/investing/index/djia');

  $quoteDate = $dom->find(".timestamp__time bg-quote")->text();

  /*
  $dji = $dom->find(".intraday__data .value")->text();
  $change = $dom->find(".intraday__data .change--point--q")->text();
  $changePercent = $dom->find(".intraday__data .change--percent--q")->text();
  */

  $group = $dom->find(".markets__group");

  $dji = $group->find(".price bg-quote")->text;
  $change = $group->find(".change bg-quote")->text;
  $changePercent = $group->find(".percent bg-quote")->text;

  // here 'stocks' is the $ar array which has
  // $ar[$stock] = ["price"=>$price, "qty"=>$qty, "status"=>$status, "company"=>$company];
  // to start and then gets the iex data added as 'moving', 'latestPrice',
  // 'change', 'changePercent', 'latestUpdate' and 'avgTotalVolume'.
  
  $ret = json_encode(array('stocks'=>$ar,
                           'dji'=>$dji,
                           'change'=>$change,
                           'per'=>$changePercent,
                           'date'=>$quoteDate
                          )
                    );
  
  echo $ret;
  exit();
}

$S = new $_site->className($_site);

checkUser($S);

$h->title = "Updating Stock Quotes";

$h->css =<<<EOF
<style>
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
/* Use Roboto Bold for the 'totals' table right below 'stocks' table */
#totals th {
  font-family: "Roboto bold";
  text-align: left;
  width: 3rem;
}
#totals th:last-child {
  text-align: right;
}
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
</style>
EOF;

$h->banner = "<h1>Stock Quotes</h1>";

// Put the js at the end just befor the closing </body>

$b->script = "<script src='stock-price-update.js'></script>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

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

<?php
// stock-price-update.php
// uses stock-price-update.js which
// uses stock-price-update-worker.js
// The worker does most of the real background work.
// BLP 2018-03-07 -- Uses Roboto from /var/www/bartonphillips.com/fonts
// BLP 2020-06-04 -- Use iex to get the avgTotalVolume and day200MovingAvg instead of reading the
// database.
// BLP 2020-09-17 -- Removed the WSJ logic for Damiler as it is available from iex
// BLP 2020-10-21 -- Include mutual funds in list returned by AJAX. Move qty and value together. In stock-price-update.js include mutual funds
// along with 'active'.

// This is for iex cloud the new way to get stock info. Which is used in the worker.
// API Token: pk_feb2cd9902f24ed692db213b2b413272 
// Account No. 7e36c73b36687b93ac549e7f828447c2 
// SECRET sk_6f07cb9018994f51a6d27eb7b27d5ebf

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

use PHPHtmlParser\Dom;

function checkUser($S) {
  //echo "cookie: ". $_COOKIE['SiteId']."<br>";
  
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

  // BLP 2020-10-21 -- include mutual funds
  
  $sql = "select stock, price, qty, status, name from stocks";
  //"where status != 'mutual'";
  
  $S->query($sql);
  $ar = [];

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow('num')) {
    $ar[] = [$stock, $price, $qty, $status, $company];
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

  //error_log("dji: $dji, change: $change, per: $changePercent, time: $quoteDate");
  
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
/* A span to show volumes that are current rather than closing */
.current {
  color: green;
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
<div>The <i>Av Price</i> is a moving average over the last 200 days. <i>Av Vol</i> is the average over last 30 days.<br>
<i>Vol</i> in <span class="current">green</span> indicates the current volume,
</div>
<div id='dji'></div>
<div id='stock-data'><img id="loading" src="https://bartonphillips.net/images/loading.gif"</img></div>
<div id='attribution'></div>
<hr>
$footer
EOF;

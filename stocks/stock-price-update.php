<?php
// stock-price-update.php
// uses stock-price-update.js which
// uses stock-price-update-worker.js
// The worker does most of the real background work.
// BLP 2018-03-07 -- Uses Roboto from /var/www/bartonphillips.com/fonts

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
use PHPHtmlParser\Dom;

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

// AJAX.
// Get Wall Street Journal DDAIF (Daimler AG)
// BLP 2018-03-25 -- TO DO:
// This is probably not the best way to do this. At some point I may not own Daimler any more. This
// should probably be moved into the 'web' AJAX below and only do this if DDAIF is in my stocks.

if($_GET['WSJ']) {
  date_default_timezone_set("America/New_York");

  $dom = new Dom;
  $dom->loadFromUrl("http://quotes.wsj.com/DDAIF");

  $quote = $dom->find("#quote_val")->text;
  $change = $dom->find("#quote_change")->text;
  $changePercent = $dom->find("#quote_changePer")->text;
  $volume = $dom->find("#quote_volume")->text;
  $quoteDate = $dom->find("#quote_dateTime")->text;
  preg_match("~(\d{1,2}:\d{2}) (..) (...) (\d{2})/(\d{2})/(\d{2})~", $quoteDate, $m);
  $str = "20$m[6]-$m[4]-$m[5] $m[1] $m[2]";
  $date =  date("Y-m-d H:i T", strtotime($str));

  $ret = ['curPrice'=>$quote, 'curChange'=>$change, 'curPercent'=>$changePercent,
          'curVol'=>$volume, 'curUpdate'=>$date];

  echo json_encode($ret);
  exit();
};

// AJAX
// Get info from stocks.stocks and DJIA (Dow Jones Industrial Average) from WSJ

if($_POST['page'] == 'web') {
  $S = new Database($_site);
  //$sql = $_POST['sql'];

  $sql = "select stock, price, qty, status, name from stocks.stocks ".
         "where stock not in('DJI','ZENO') and status != 'mutual'";
  
  $S->query($sql);
  $ar = [];
  $r = $S->getResult();

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow($r, 'num')) {
    $sql = "select volume, price from stocks.pricedata where stock='$stock' order by date desc limit 100";
    $S->query($sql);

    for($cnt=0, $avVol=0, $avPrice=0; list($volume, $p) = $S->fetchrow('num'); ++$cnt) {
      $avVol += $volume;
      $avPrice += $p;
    }
    $avVol = round($avVol / $cnt);
    $avPrice = round($avPrice / $cnt, 2);
    
    $ar[] = [$stock, $price, $qty, $status, $company, $avVol, $avPrice];
  }

  // use Dom to scrape the wsj site of the DJIA info.
  
  $dom = new Dom;
  $dom->loadFromUrl('http://quotes.wsj.com/index/DJIA');
  $dji = $dom->find("#quote_val")->text;
  $change = $dom->find("#quote_change")->text;
  $changePercent = $dom->find("#quote_changePer")->text;
  $quoteDate = $dom->find("#quote_dateTime")->text;
  
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
/* I use a '%' in a span to pad the right side of price/% fields. Make it invisible */
.noper {
  visibility: hidden;
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

$b->script = "<script src='./stock-price-update.js'></script>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<hr>
<div id='dji'></div>
<div id="selectstatus"></div>
<div>The <i>Av Price</i> and <i>Av Vol</i> are computed over the last 100 days.<br>
<i>Vol</i> in <span class="current">green</span> indicates the current volume,
otherwise it is the closing volume from the last trading day.</div>
<div id='stock-data'><img id="loading" src="https://bartonphillips.net/images/loading.gif"</img></div>
<hr>
$footer
EOF;

/*
stdClass Object
(
  [BA] => stdClass Object
    (
      [quote] => stdClass Object
        (
          [symbol] => BA
          [companyName] => The Boeing Company
          [primaryExchange] => New York Stock Exchange
          [sector] => Industrials
          [calculationPrice] => sip
          [open] => 366.47
          [openTime] => 1519828210380
          [close] => 362.21
          [closeTime] => 1519851621062
          [high] => 
          [low] => 
          [latestPrice] => 361.16
          [latestSource] => 15 minute delayed price
          [latestTime] => 8:41:40 AM
          [latestUpdate] => 1519911700953
          [latestVolume] => 17060
          [iexRealtimePrice] => 0
          [iexRealtimeSize] => 0
          [iexLastUpdated] => 0
          [delayedPrice] => 361.16
          [delayedPriceTime] => 1519911700953
          [previousClose] => 362.21
          [change] => -1.05
          [changePercent] => -0.0029
          [iexMarketPercent] => 0
          [iexVolume] => 0
          [avgTotalVolume] => 6255375
          [iexBidPrice] => 0
          [iexBidSize] => 0
          [iexAskPrice] => 0
          [iexAskSize] => 0
          [marketCap] => 212539161443
          [peRatio] => 29.9
          [week52High] => 371.6
          [week52Low] => 173.75
          [ytdChange] => 0.22021964694785
        )
    )
)
*/
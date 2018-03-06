<?php
// stock-price-3.php
// uses stock-price-3.js which
// uses stock-price-3-worker.js
// The worker does most of the real background work.

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

//error_log("POST: ".print_r($_POST, true));

if($_POST['page'] == 'web') {
  $S = new Database($_site);
  //$sql = $_POST['sql'];

  $sql = "select stock, price, qty, status, name from stocks.stocks ".
         "where stock not in('DJI','ZENO') and status != 'mutual'";
  
  $S->query($sql);
  $ar = [];
  $r = $S->getResult();

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow($r, 'num')) {
    $stk = preg_replace("/-BLP/", '', $stock);

    $sql = "select volume, price from stocks.pricedata where stock='$stk' order by date desc limit 100";
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
/*@font-face {
  font-family: "San Fransisco";
  src: url("/fonts/SanFranciscoFont/SanFranciscoText-Regular.otf") format("opentype");
}
*/
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
/*
       url("/fonts/Roboto/Roboto-BoldItalic.ttf"),
       url("/fonts/Roboto/Roboto-Italic.ttf"),
       url("/fonts/Roboto/Roboto-LightItalic.ttf"),
       url("/fonts/Roboto/Roboto-Medium.ttf"),
       url("/fonts/Roboto/Roboto-MediumItalic.ttf"),
       url("/fonts/Roboto/Roboto-Thin.ttf"),
       url("/fonts/Roboto/Roboto-ThinItalic.ttf");
*/

body {
  font-family: "Roboto";
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
#stocks th {
  padding: .3rem;
}
/* The first td has stock and company each in a span. This is the second span which is company */
#stocks td:first-child span:last-child {
  font-family: "Roboto bold";
  font-size: .5rem;
  line-height: 0;
  text-transform: capitalize;
}
select {
  font-size: 1rem;
}
#stocks td:first-child {
  font-family: "Roboto bold";
  text-align: left;
  line-height: 60%;
}
#stocks td:nth-child(4) {
  font-family: "Roboto bold";
}
#totals th {
  font-family: "Roboto bold";
  text-align: left;
  width: 3rem;
}
#totals th:last-child {
  text-align: right;
}
.neg {
  color: red;
}
.small {
  font-size: .8rem;
}
.current {
  color: green;
}
.noper {
  visibility: hidden;
}
</style>
EOF;

$h->banner = "<h1>Stock Prices</h1>";

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
<div id='stock-data'></div>
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
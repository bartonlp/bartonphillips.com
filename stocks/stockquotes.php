<?php
// stockquotes.php
// This is the OLD version. The new version is 'stock-price-update.php'
// This does a singe access and does NOT refresh every 5min.

// IEX: https://iextrading.com/
// https://iextrading.com/developer/docs/

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);
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

checkUser($S);

$h->title = "Stock Quotes";
$h->banner = "<h1>Stock Quotes</h1>";

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  $(".stock").click(function(e) {
    var stk = $(this).text();

    var url = "https://www.marketwatch.com/investing/stock/"+stk; //"https://finance.yahoo.com/quote/"+stk+"/";
    var w1 = window.open(url, '_blank');
    return false;
  });
});
  </script>
EOF;

$h->css =<<<EOF
  <style>
#stocktable, #watchtable {
  width: 100%;
}
#stocktable td:nth-child(1), #watchtable td:nth-child(1) {
  width: 20rem;
}
#stocktable th, #stocktable td,
#watchtable th, #watchtable td {
  padding: .2rem;
}
#stocktable td:nth-child(2), #watchtable td:nth-child(2) {
  text-align: center;
}
#stocktable td:nth-child(3), #watchtable td:nth-child(3) {
  text-align: right;
}
#stocktable td:nth-child(4), #watchtable td:nth-child(4) {
  text-align: right;
}
#stocktable td:nth-child(5), #watchtable td:nth-child(5) {
  text-align: right;
}
#stocktable td:nth-child(6) {
  text-align: right;
}
#stockname {
  font-size: 10px;
}
.stock {
  cursor: pointer;
  background-color: lightblue;
}
.negchange {
  color: red;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

date_default_timezone_set("America/New_York");
$currDate = date("l j F Y H:i");

$prefix = "https://api.iextrading.com/1.0";

/*
Dow Jones 30
AA AXP BA BAC CAT CSCO CVX DD DIS GE HD
HPQ IBM INTC JNJ JPM KFT KO MCD MMM MRK 
MSFT PFE PG T TRV UTX VZ WMT XOM
Divisor: 0.132129493
*/

$sql = "select stock, price, qty from stocks.stocks where status not in('mutual','watch','sold')";
$S->query($sql);

$stocks = [];

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  // NOTE Alpha needs RDS-A while iex wants RDS.A

  $stock = ($stock == "RDS-A") ? "RDS.A" : $stock;
  $stocks[$stock] = [$price, $qty];
}

$arkeys = array_keys($stocks);

$str = "$prefix/stock/market/batch?symbols=" . implode(',', array_values($arkeys)) . "&types=quote";

$h = curl_init();
curl_setopt($h, CURLOPT_URL, $str);
curl_setopt($h, CURLOPT_HEADER, 0);
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

// We get the stocks from the array above  
$ret = curl_exec($h);

$ar = json_decode($ret);

$dom = new Dom;
$dom->loadFromUrl('http://quotes.wsj.com/index/DJIA');
$dji = number_format($dom->find("#quote_val")->text);
$djichange = number_format($dom->find("#quote_change")->text, 2);

$quotes = '';

foreach($stocks as $sym=>$stock) {
  $v = $ar->$sym;
  
  $qt = $v->quote;
  
  $date = date("Y-m-d H:i:s", $qt->latestUpdate / 1000);

  // The close and previousClose can be different after the close of market on the current date but
  // will be the same once the market opens the next day.

  $changeTotal += $qt->change * $stock[1]; // qty
  
  if($qt->change < 0) {
    $change = "<span class='negchange'>". number_format($qt->change, 2). ", ".
              number_format($qt->changePercent * 100, 2)."%</span>";
  } else {
    $change = number_format($qt->change, 2) . ", " . number_format($qt->changePercent * 100, 2) . "%";
  }
  
  $close = number_format($qt->previousClose, 2) ."<br>" . $change;

  $price = round($qt->latestPrice, 2); // raw price
  $pricex = number_format($price, 2); // format to 2 deciaml places.
  $company = $qt->companyName;
  $sector = $qt->sector;
  $volume = number_format($qt->latestVolume, 0);
  
  $value = number_format($stock[1] * $price, 2)."<br>".number_format($stock[1]);

  $orgprice = $stock[0];
  $percent = ($price - $orgprice) / $orgprice;
  if($percent < 0) {
    $percent = "<span class='negchange'>". number_format($percent * 100, 2)."%</span>";
  } else {
    $percent = number_format($percent * 100, 2) . "%";
  }
  $orgprice = number_format($orgprice, 2);

  $quotes .= "<tr><td><span class='stock'>$sym</span><div id='stockname'>$company<br>$sector</div></td><td>$date</td>".
             "<td>$pricex</td><td>$value</td><td>$orgprice<br>$percent</td>".
             "<td>$volume</td><td>$close</td></tr>";
}

$tmp = $changeTotal;

$changeTotal = number_format($changeTotal, 2);
$changePercent = number_format($changePercent * 100, 2) . "%";

if($tmp < 0) {
  $changeTotal = "<span class='negchange'>$changeTotal</span>";
}

$sql = "select stock, price, qty from stocks.stocks where status not in('active','mutual','sold')";
$S->query($sql);

$stocks = [];

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  $stocks[$stock] = [$price, $qty];
}
// Remove DJI average.
unset($stocks['DJI']);

$str = "$prefix/stock/market/batch?symbols=" . implode(',', array_keys($stocks)) . "&types=quote";
curl_setopt($h, CURLOPT_URL, $str);
$ret = curl_exec($h);
$ar = json_decode($ret);

$watchquote = '';

foreach($ar as $k=>$v) {
  $qt = $v->quote;
  //vardump($qt);

  $st = $qt->symbol;
  $date = date("Y-m-d H:i:s", $qt->latestUpdate / 1000);
  // The close and previousClose can be different after the close of market on the current date but
  // will be the same once the market opens the next day.

  $watchTotal += $qt->change;
  $watchPercent += $qt->changePercent;

  if($qt->change < 0) {
    $qt->change = "<span class='negchange'>".number_format($qt->change, 2). ", ".
                  number_format($qt->changePercent * 100, 2)."%</span>";
  } else {
    $qt->change = number_format($qt->change, 2).", ". number_format($qt->changePercent * 100, 2). "%";
  }
  
  $close = $qt->previousClose ."<br>" . $qt->change;
  
  $price = $qt->latestPrice; // raw price
  $pricex = number_format($price, 2); // format to 2 deciaml places.
  $company = $qt->companyName;
  $sector = $qt->sector;
  $volume = number_format($qt->latestVolume, 0);

//  $stock = $stocks[$k];
//  $value = number_format($stock[1] * $price, 2)."<br>".number_format($stock[1]);
  $watchquote .= "<tr><td><span class='stock'>$st</span><div id='stockname'>$company<br>$sector</div></td><td>$date</td>".
                  "<td>$pricex</td><td>$volume</td><td>$close</td></tr>";
}

echo <<<EOF
$top
<h3>Dow Jones Average: $dji, Change: $djichange</h3>
<h4>$currDate</h4>
<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol<br>Info</th><th>Last Trade</th><th>Price</th><th>Value<br>Qty</td>
  <th>OrgPrice<br>Change %</th><th>Volume</th><th>Close<br>Change</th></tr>
</thead>
<tbody>
$quotes
</tbody>
</table>
<p>Change in my holdings: $$changeTotal</p>
<hr>
<h3>Watch Stocks</h3>
<table id="watchtable" border="1">
<thead>
<tr><th>Sybmol<br>Info</th><th>Last Trade</th><th>Price</th>
  <th>Vol</th><th>Close<br>Change</th></tr>
</thead>
<tbody>
$watchquote
</tbody>
</table>
<hr>
$footer
EOF;

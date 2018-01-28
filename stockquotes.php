<?php
// IEX: https://iextrading.com/
// https://iextrading.com/developer/docs/

// alpha vantage api key: FLT73FUPI9QZ512V
$alphakey = "FLT73FUPI9QZ512V";
// https://www.alphavantage.co/documentation
// https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=MSFT&interval=1min&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=MSFT&apikey=demo
// https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=MSFT&apikey=demo

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$h->title = "Stock Quotes";
$h->banner = "<h1>Stock Quotes</h1>";

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  $(".stock").click(function(e) {
    var stk = $(this).text();
    stk = stk.replace(/-BLP/, '');

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
$arkeys = preg_replace("/-BLP/", "", $arkeys);

$str = "$prefix/stock/market/batch?symbols=" . implode(',', array_values($arkeys)) . "&types=quote";

$h = curl_init();
curl_setopt($h, CURLOPT_URL, $str);
curl_setopt($h, CURLOPT_HEADER, 0);
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

// We get the stocks from the array above  
$ret = curl_exec($h);

$ar = json_decode($ret);

// Now we get the DJI for the day.
$alp = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=DJI&apikey=$alphakey";
curl_setopt($h, CURLOPT_URL, $alp);
$alpha = curl_exec($h);

$alpha = json_decode($alpha, true); // decode as an array

$name = $alpha["Meta Data"]["2. Symbol"];

$djidate = array_keys($alpha["Time Series (Daily)"])[0];
$ar1 = array_values($alpha["Time Series (Daily)"]);
$djiclose = $ar1[0]["4. close"];
$djiprevclose = $ar1[1]["4. close"];
$djichange = $djiclose - $djiprevclose;
$djichangePercent = number_format((($djiclose - $djiprevclose) / $djiclose) * 100, 2);

if($djichange < 0) {
  $djichange = "<span class='negchange'>".number_format($djichange, 3).", $djichangePercent%</span>";
} else {
  $djichange = number_format($djichange, 3) . ", $djichangePercent%";
}

$djiclose = round($djiclose, 2);
$djiclose = number_format($djiclose, 2);

$quotes = '';

foreach($stocks as $sym=>$stock) {
  // 1) remove -BLP from the key to $ar which is from iex
  $key = preg_replace("/-BLP/", "", $sym);
  $v = $ar->$key;
  
  $qt = $v->quote;
  
  // 2) here we use the symbol with -BLP for the display
  $st = $sym;
  
  $date = date("Y-m-d H:i:s", $qt->latestUpdate / 1000);

  // The close and previousClose can be different after the close of market on the current date but
  // will be the same once the market opens the next day.

  $changeTotal += $qt->change;
  $changePercent += $qt->changePercent;
  
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

  $value = number_format($stock[1] * $price, 2)."<br>".number_format($stock[1]);

  $orgprice = $stock[0];
  $percent = ($price - $orgprice) / $orgprice;
  if($percent < 0) {
    $percent = "<span class='negchange'>". number_format($percent * 100, 2)."%</span>";
  } else {
    $percent = number_format($percent * 100, 2) . "%";
  }
  $orgprice = number_format($orgprice, 2);

  // Depending on who we use for detailed report this may be needed.
  // MarketWatch uses RDS.A but yahoo need it as RDS-A.
  // $st = ($st == 'RDS.A') ? "RDS-A" : $st;

  $quotes .= "<tr><td><span class='stock'>$st</span><div id='stockname'>$company<br>$sector</div></td><td>$date</td>".
             "<td>$pricex</td><td>$value</td><td>$orgprice<br>$percent</td><td>$close</td></tr>";
}

$tmp = $changeTotal;

$changeTotal = number_format($changeTotal, 2);
$changePercent = number_format($changePercent * 100, 2) . "%";
$changeTotal = "$changeTotal, $changePercent";

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

//  $stock = $stocks[$k];
//  $value = number_format($stock[1] * $price, 2)."<br>".number_format($stock[1]);
  $watchquote .= "<tr><td><span class='stock'>$st</span><div id='stockname'>$company<br>$sector</div></td><td>$date</td>".
                  "<td>$pricex</td><td>$close</td></tr>";
}

echo <<<EOF
$top
<p>$name for $djidate: $djiclose, Change: $djichange</p>
<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol<br>Info</th><th>Last Trade</th><th>Price</th><th>Value<br>Qty</td>
  <th>OrgPrice<br>Change %</th><th>Close<br>Change</th></tr>
</thead>
<tbody>
$quotes
</tbody>
</table>
<p>Change in my holdings: $changeTotal</p>
<hr>
<h3>Watch Stocks</h3>
<table id="watchtable" border="1">
<thead>
<tr><th>Sybmol<br>Info</th><th>Last Trade</th><th>Price</th>
  <th>Close<br>Change</th></tr>
</thead>
<tbody>
$watchquote
</tbody>
</table>
<hr>
$footer
EOF;

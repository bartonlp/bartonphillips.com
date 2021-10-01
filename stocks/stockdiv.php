<?php
// stockdiv.php
// Use IEX to get the dividend info.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

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

checkUser($S);

date_default_timezone_set("America/New_York");

$prefix = "https://cloud.iexapis.com/stable";
$token = "token=pk_feb2cd9902f24ed692db213b2b413272";

$sql = "select stock, price, qty from stocks where status not in('watch','sold')";
$S->query($sql);

$ar = [];
$stocks = "";

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  if($stock == "RDS-A") $stock = "RDS.A";
  $stocks .= "$stock,";
  $ar[$stock] = ['price'=>$price, 'qty'=>$qty];
}


$totalyield = 0;
$totalcnt = 0;

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$str = "$prefix/stock/market/batch?symbols=$stocks&types=quote,stats&filter=latestPrice,companyName,ttmDividendRate,dividendYield,exDividendDate&$token";

curl_setopt($ch, CURLOPT_URL, $str);
$ret = curl_exec($ch);
$ret = json_decode($ret);

foreach($ret as $k=>$v) {
  $price = $ar[$k]['price'];
  $qty = $ar[$k]['qty'];
  $close = number_format($v->quote->latestPrice, 2);
  
  $v = $v->stats;
  $company = $v->companyName;

  $divyield = number_format($v->dividendYield,2) * 100 . "%";
  $divxdiv = substr($v->exDividendDate, 0, 10);

  $div = $v->ttmDividendRate;
  $totalyield += $orgyield = ($div / $price) * 100;
  
  $totalcnt++;
  $orgyield = number_format($orgyield, 2). "%";
  
  $total += $ern = $div * $qty;

  $div = number_format($v->ttmDividendRate, 2);
  
  $ern = number_format($ern, 2);

  // $st is how it is in the database we may need to fix this in the javascript if we use Yahoo
  
  $quotes .= "<tr><td class='stock'><span>$k</span><br>$company</td><td>$price</td><td>$qty</td>".
             "<td>$div</td><td>$orgyield</td><td>$close</td>".
             "<td>$divyield</td><td>$divxdiv</td><td>$ern</tr>";
}

$h->title = "Stock Dividends";
$h->banner = "<h1>Stock Dividends</h1>";

$h->css =<<<EOF
  <style>
#stocktable {
  width: 100%;
}
#stocktable td:nth-child(1) {
}
#stocktable th {
}
#stocktable td {
  padding: .2rem .5rem;
}
#stocktable td:nth-child(2) {
  text-align: right;
}
#stocktable td:nth-child(3) {
  text-align: right;
}
#stocktable td:nth-child(4) {
  text-align: right;
}
#stocktable td:nth-child(5) {
  text-align: right;
}
#stocktable td:nth-child(6) {
  text-align: right;
  background-color: lightgreen;
}
#stocktable td:nth-child(7) {
  text-align: right;
  background-color: lightgreen;
}
#stocktable td:nth-child(8) {
  text-align: right;
}
#stocktable td:nth-child(9) {
  text-align: right;
}
.stock {
  cursor: pointer;
  background-color: lightblue;
}
  </style>
EOF;

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  // Remove message. If it isn't there no problem.

  $("body").on('click', function(e) {
    $("#message").remove();
  });

  $(".stock").on('click', function(e) {
    let stk = $('span', this).text();
    // For MarketWatch we need RDS.A, BUT for Yahoo we need RDS-A
    // We are using marketwatch.com right now. If we use Yahoo then comment out the if(stk...
    if(stk == 'RDS-A') stk='RDS.A';
    var url = "https://www.marketwatch.com/investing/stock/"+stk; 
    var w1 = window.open(url, '_blank');
    return false;
  });
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$total = number_format($total, 2);
$avyield = number_format($totalyield / $totalcnt, 2);

echo <<<EOF
$top
<p>Left <b>click</b> on the symbol name to goto <i>www.marketwatch.com/investing/stock/</i>
for that stock.</p>

<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol</th><th>Buy Price</th><th>Qty</th><th>Dividend</th><th>Buy Yield</th>
<th>LatestPrice</th><th>Curr Yield</th><th>X Div Date</th><th>Ernings</th></tr>
</thead>
<tbody>
$quotes
</tbody>
</table>
<p>Total Ernings:     $total<br>
   Average Buy Yield: ${avyield}%</p>
<hr>
$footer
EOF;

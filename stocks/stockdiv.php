<?php
// stockdiv.php
// Use IEX to get the dividend info.
// BLP 2021-11-04 -- get ipx token from secure location.

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

//BLP 2022-01-21 -- Get iex-token form my secure location

$token = require_once("/var/www/PASSWORDS/iex-token");
//$token = file_get_contents("https://bartonphillips.net/PASSWORDS/iex-token.php");
//$sql = "select stock, price, qty, status from stocks where status not in('watch','sold')";
$sql = "select stock, price, qty, status from stocks where status = 'active'";
$S->query($sql);

$ar = [];
$stocks = "";

while([$stock, $price, $qty, $status] = $S->fetchrow('num')) {
  $stocks .= "$stock,";
  $ar[$stock] = ['price'=>$price, 'qty'=>$qty, 'status'=>$status];
}

$totalyield = 0;
$total = 0;
$totalcnt = 0;

$str = "$prefix/stock/market/batch?symbols=$stocks&types=quote,stats&filter=latestPrice,companyName,ttmDividendRate,dividendYield,exDividendDate&token=$token";

$ret = json_decode(file_get_contents($str));

foreach($ret as $k=>$v) {
  $price = $ar[$k]['price'];
  $qty = $ar[$k]['qty'];
  $status = $ar[$k]['status'];
  
  $close = number_format($v->quote->latestPrice, 2);
  
  $v = $v->stats;
  $company = $v->companyName;

  $divyield = number_format($v->dividendYield,2) * 100 . "%";
  $divxdiv = substr($v->exDividendDate, 0, 10);

  $div = $v->ttmDividendRate; // Total $ per year per one share.

  $orgyield = ($div / $price) * 100; // total $ per share dividend divided by my original price * 100 is percent.

  $y = number_format($orgyield, 2). "%";
  $e = $div * $qty; // total $ per share dividend times the quantity is $ estimated-earning per YEAR.
  $ern = number_format($e, 2);
  $totalyield += $orgyield;
  
  $total += $e;

  $orgyield = $y;
  
  $totalcnt++;

  $div = number_format($v->ttmDividendRate, 2);
  
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
  background: lightblue;
  border-collapse: collapse; /* to allow a border on the tfoot tr */
  border: 5px solid black;
}
#stocktable tr { background: white; border: 1px solid black}
#stocktable td, #stocktable th {
  border: 1px solid black;
  padding: 2px 5px;
}
#stocktable tbody td:nth-of-type(2) {
  text-align: right;
}
#stocktable tbody td:nth-of-type(3) {
  text-align: right;
}
#stocktable tbody td:nth-of-type(4) {
  text-align: right;
}
#stocktable tbody td:nth-of-type(5) {
  text-align: right;
}
#stocktable tbody td:nth-of-type(6) {
  text-align: right;
  background-color: lightgreen;
}
#stocktable tbody td:nth-of-type(7) {
  text-align: right;
  background-color: lightgreen;
}
#stocktable tbody td:nth-of-type(8) {
  text-align: right;
}
#stocktable tbody td:nth-of-type(9) {
  text-align: right;
}
/* for the footer */  
#stocktable tfoot tr { border: 5px solid black; } /* only if border-collapse: collapse; */
#stocktable tfoot th { text-align: left; }
#stocktable tfoot td { text-align: right; }
#stocktable tfoot td:nth-of-type(1) { border: none; background: lightblue; }
#stocktable tfoot td:nth-of-type(2) { vertical-align: top; }
#stocktable tfoot td:nth-of-type(3) { border: none; background: lightblue; }
#stocktable tfoot td:nth-of-type(4) { vertical-align: bottom; }
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
    var url = "https://www.marketwatch.com/investing/stock/"+stk; 
    var w1 = window.open(url, '_blank');
    return false;
  });
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$total = number_format($total, 2);
$totalReinvested = "<span style='color: red'>" . number_format($totalReinvested, 2) . "</span>";
$avyield = number_format($totalyield / $totalcnt, 2);

echo <<<EOF
$top
<p>Left <b>click</b> on the symbol name to goto <i>www.marketwatch.com/investing/stock/</i>
for that stock.</p>
<p>Note: 'Estimate Ernings per Year" for mutual funds are reinvested and are show as a seperate total at the bottom.</p>

<table id="stocktable">
<thead>
<tr><th>Sybmol</th><th>Buy Price</th><th>Qty</th><th>Dividend<br>per Year<br>per Share</th><th>Buy Yield</th>
<th>Latest<br>Price</th><th>Curr Yield</th><th>X Div Date</th><th>Estimated<br>Earnings<br>per Year</th></tr>
</thead>
<tbody>
$quotes
</tbody>
<tfoot>
<tr>
<th>Average Buy Yield<br>Total End of Year Earnings</th>
<td colspan='3'><td>${avyield}%</td>
<td colspan='3'></td><td>$total</td>
</tr>
</tfoot>
</table>
$footer
EOF;

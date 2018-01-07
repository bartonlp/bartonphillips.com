<?php
// Use IEX to get the dividend info.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$h->title = "Stock Dividends";
$h->banner = "<h1>Stock Dividends</h1>";

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
#stocktable {
  width: 100%;
}
#stocktable td:nth-child(1) {
}
#stocktable th, #stocktable td {
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
  text-align: center;
}
#stocktable td:nth-child(7) {
  text-align: right;
}
#stockname {
  font-size: 10px;
}
.stock {
  cursor: pointer;
  background-color: lightblue;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

date_default_timezone_set("America/New_York");

$prefix = "https://api.iextrading.com/1.0";

$sql = "select stock, price, qty from stocks.stocks where status not in('mutual','watch','sold')";
$S->query($sql);

$h = curl_init();
curl_setopt($h, CURLOPT_HEADER, 0);
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  $stock = $stock == "RDS-A" ? "RDS.A" : $stock;
  $stock = preg_replace("/-BLP/", "", $stock);
  
  $str = "$prefix/stock/$stock/stats";

  curl_setopt($h, CURLOPT_URL, $str);
  $ret = curl_exec($h);
  $ret = json_decode($ret);

  $key = preg_replace("/-BLP/", "", $stock);
  
  $div = number_format($ret->dividendRate, 2);
  $divyield = number_format($ret->dividendYield,2) . "%";
  $divxdiv = substr($ret->exDividendDate, 0, 10);
  
  // Depending on who we use for detailed report this may be needed.
  // For example for yahoo we need it as RDS-A.
  //if($st == 'RDS.A') $st = "RDS-A";

  $total += $ern = $div * $qty;
  $ern = number_format($ern, 2);
  
  $quotes .= "<tr><td><span class='stock'>$key</span></td><td>$price</td><td>$qty</td>".
             "<td>$div</td><td>$divyield</td><td>$divxdiv</td><td>$ern</tr>";
}

$total = number_format($total, 2);

echo <<<EOF
$top
<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol</th><th>Price</th><th>Qty</th><th>Dividend</th>
<th>Yield</th><th>X Div Date</th><th>Ernings</th></tr>
</thead>
<tbody>
$quotes
</tbody>
</table>
<p>Total Ernings: $total</p>
<hr>
$footer
EOF;

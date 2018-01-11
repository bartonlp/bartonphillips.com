<?php
// Use IEX to get the dividend info.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

date_default_timezone_set("America/New_York");

$prefix = "https://api.iextrading.com/1.0";

$sql = "select stock, price, qty from stocks.stocks where status not in('mutual','watch','sold')";
$S->query($sql);

$totalyield = 0;
$totalcnt = 0;

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  $stock = $stock == "RDS-A" ? "RDS.A" : $stock;
  $stock = preg_replace("/-BLP/", "", $stock);
  
  $str = "$prefix/stock/$stock/stats";

  curl_setopt($ch, CURLOPT_URL, $str);
  $ret = curl_exec($ch);
  $ret = json_decode($ret);

  $key = preg_replace("/-BLP/", "", $stock);

  $company[$key] = $ret->companyName;
  
  $div = number_format($ret->dividendRate, 2);
  $divyield = number_format($ret->dividendYield,2) . "%";
  $divxdiv = substr($ret->exDividendDate, 0, 10);

  $totalyield += $orgyield = ($ret->dividendRate / $price) * 100;
  $totalcnt++;

  $orgyield = number_format($orgyield, 2). "%";
  
  // Depending on who we use for detailed report this may be needed.
  // For example for yahoo we need it as RDS-A.
  //if($st == 'RDS.A') $st = "RDS-A";

  $total += $ern = $div * $qty;
  $ern = number_format($ern, 2);
  
  $quotes .= "<tr><td class='stock'><span>$key</span></td><td>$price</td><td>$qty</td>".
             "<td>$div</td><td>$orgyield</td><td>$divyield</td><td>$divxdiv</td><td>$ern</tr>";
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
  text-align: right;
}
#stocktable td:nth-child(7) {
  text-align: right;
}
#stocktable td:nth-child(8) {
  text-align: right;
}
.stock {
  cursor: pointer;
  background-color: lightblue;
}
  </style>
EOF;

$jsoncompany = json_encode($company);

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  var companyName = JSON.parse('$jsoncompany');

  // Remove message. If it isn't there no problem.

  $("body").on('click', function(e) {
    $("#message").remove();
  });

  // Clicked on the first column 'Symbol'

  $(".stock").on('contextmenu', function(e) {
    let stk = $('span', this).text();

    let pos = $(this).position(),
        width = $(this).innerWidth(),
        xpos = pos.left+width,
        ypos = pos.top,
        company = companyName[stk];

    $("#message").remove();
    $("<div id='message' style='position: absolute; "+
      "left: "+xpos+"px; top: "+ypos+"px; "+
      "background-color: white; border: 5px solid black;padding: 10px'>"+
      company+"</div>").appendTo($(this));

    e.stopPropagation();
    return false;
  });

  $(".stock").on('click', function(e) {
    let stk = $('span', this).text();
    stk = stk.replace(/-BLP/, '');
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
for that stock.<br>
Right <b>click</b> on the symbol name to see the stock <i>Name</i></p>

<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol</th><th>Buy Price</th><th>Qty</th><th>Dividend</th><th>Buy Yield</th>
<th>Curr Yield</th><th>X Div Date</th><th>Ernings</th></tr>
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
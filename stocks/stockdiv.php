<?php
// stockdiv.php
// Use IEX to get the dividend info.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

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

date_default_timezone_set("America/New_York");

$prefix = "https://api.iextrading.com/1.0";

$sql = "select stock, price, qty from stocks.stocks where status not in('mutual','watch','sold')";
$S->query($sql);

$totalyield = 0;
$totalcnt = 0;

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$r = $S->getResult();

// The 'stocks' table has 'RDS-A' and iex needs 'RDS.A'

while(list($stock, $price, $qty) = $S->fetchrow($r, 'num')) {
  $st = $stock; // Display as it is in the database 'RDS-A'
  if($stock == "RDS-A") $stock = "RDS.A"; // For iex
  
  $str = "$prefix/stock/$stock/stats";

  curl_setopt($ch, CURLOPT_URL, $str);
  $ret = curl_exec($ch);
  $ret = json_decode($ret);

  $company[$stock] = $ret->companyName;
  
  $div = number_format($ret->dividendRate, 2);
  $divyield = number_format($ret->dividendYield,2) . "%";
  $divxdiv = substr($ret->exDividendDate, 0, 10);

  $totalyield += $orgyield = ($ret->dividendRate / $price) * 100;
  $totalcnt++;

  $orgyield = number_format($orgyield, 2). "%";
  
  $total += $ern = $div * $qty;
  $ern = number_format($ern, 2);

  $sql = "select price from stocks.pricedata ".
         "where lasttime > current_date() - interval 1 day and stock='$stock'";
  
  $S->query($sql);
  list($close) = $S->fetchrow('num');
  $close = number_format($close, 2);

  // $st is how it is in the database we may need to fix this in the javascript if we use Yahoo
  
  $quotes .= "<tr><td class='stock'><span>$st</span></td><td>$price</td><td>$qty</td>".
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
for that stock.<br>
Right <b>click</b> on the symbol name to see the stock <i>Name</i></p>

<table id="stocktable" border="1">
<thead>
<tr><th>Sybmol</th><th>Buy Price</th><th>Qty</th><th>Dividend</th><th>Buy Yield</th>
<th>Yest. Close</th><th>Curr Yield</th><th>X Div Date</th><th>Ernings</th></tr>
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

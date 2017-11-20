<?php
// analyze the stocks in the 'pricedata' table

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$move = 100;

if($_GET['move']) {
  $move = $_GET['move'];
}

// Get all of the stocks in my portfolio

$sql = "select stock, status from stocks.stocks";
$S->query($sql);
$r = $S->getResult(); // Save result

$an = [];

// Loop through each stock

while(list($stock, $status) = $S->fetchrow($r, 'num')) {
  if($stock == "RDS.A") $stock = "RDS-A";
  
  $sql = "select stock, date, price, price from stocks.pricedata where stock='$stock' ".
         "order by date desc limit $move";
  
  $S->query($sql);

  // Loop through this stock and save info in $an
  
  while(list($stock, $date, $price) = $S->fetchrow('num')) {
    if($stock == "DJI") $stock = "DJI-AVG";

    $an[$stock][] = (object)array('price'=>"$price", 'date'=>"$date", 'status'=>$status);
  }
}

// Now loop through all of the $an stocks

$kk = null;
$moving = '';

foreach($an as $k=>$v) {
  // If $k != $kk then this is a new stock
  
  if($k != $kk) {
    // But if $kk is null this is the first time through so don't show the info until next time.
    
    if($kk) {
      // watch the order of formating. Must do $price after $av
      $av = $price / $c;
      $percent = ($lastprice - $av) / $av; //$lastprice;

      $percent = $percent < 0
                 ? "<span class='negchange'>" .number_format($percent * 100, 2)."</span>"
                 : number_format($percent * 100, 2);
      
      $av = number_format($av, 2);
      $lastprice = number_format($lastprice, 2);
      $price = number_format($price, 2);

      // make the table rows
      $tr = "<tr>";
      if($status == 'watch') {
        $tr = "<tr class='watch'>";
      }
      $moving .= "$tr<td>".($kk ?: $k)."</td><td>$c</td><td>$av</td>".
                 "<td>$lastprice</td><td>$percent</td><td>$status</td></tr>";
    }
    // $k != $kk so new stock
    $kk = $k;
    $c = 0;
    $price = 0;
  }
  // $k == $kk so just accumulate price info
  
  foreach($v as $vv) {
    $price += $vv->price;
    if($c == 0) {
      $lastprice = $vv->price;
    }
    $status = $vv->status;
    $c++;
  }
}

$h->title = "Stock Moving Average";
$h->banner = "<h1>Moving Average</h1>";
$h->css =<<<EOF
  <style>
#moving td, #moving th {
  padding: .2rem;
}
#moving td {
  text-align: right;
}
#moving td:nth-child(1) {
  text-align: left;
}
.negchange {
  color: red;
}
.watch {
  background-color: #FFEFD5;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Render page

echo <<<EOF
$top
<table id='moving' border="1">
<thead>
<tr><th>Stock</th><th>Count</th><th>Moving</th><th>Last Price</th><th>Change %</th><th>Status</th></tr>
</thead>
<tbody>
$moving
</tbody>
</table>
<hr>
$footer
EOF;

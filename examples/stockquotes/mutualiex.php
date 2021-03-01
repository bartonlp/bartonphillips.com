<?php
// mutual.php
// Get the mutual fund info from Wall Street Journal
// Does a file_get_contents() POST

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);

if($_POST['mutual']) {
  $mutual = $_POST['mutual'];
  
  $url = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=". $mutual .
              "&types=quote,stats&filter=latestPrice,change,changePercent,latestUpdate,".
              "day200MovingAvg".
              "&token=pk_feb2cd9902f24ed692db213b2b413272";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  $ret = curl_exec($ch);
  echo $ret;
  exit;
  // In lieu of AJAX. This does a GET from url.
};

$h->title = "Mutual Funds Prices";
$h->css =<<<EOF
<style>
#mutual {
  border: 1px solid black;
}
#mutual * {
  border: 1px solid black;
  padding: .3rem;
}
#mutual td {
  text-align: right;
}
#mutual td:first-child {
  text-align: left;
}
</style>
EOF;
$h->banner = "<h1>Mutual Funds Info</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<hr>
<div>
EOF;

$mutual = "";

$sql = "select stock, qty from stocks.stocks where status = 'mutual'";

$S->query($sql);

while(list($stock, $qty) = $S->fetchrow("num")) {
  $mutual .= "$stock,";
  $stocks->$stock = $qty;
}

$mutual = rtrim($mutual, ',');

$tbl = <<<EOF
<table id='mutual'>
<thead>
<tr><th>Name</th><th>Price</th><th>Moving</th><th>Qty<br>Amount</th><th>Change<br>%</th><th>latest</th></tr>
</thead>
<tbody>
EOF;

$total = 0;

$options = array('http' => array(
                                 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                 'method'  => 'POST',
                                 'content' => http_build_query(array('mutual'=>$mutual))
                                )
                );

$context  = stream_context_create($options);

// Now this is going to do a POST!

$info = file_get_contents("https://www.bartonphillips.com/examples/stockquotes/mutualiex.php",
                            false, $context);

$info = json_decode($info, true);

foreach($info as $k=>$v) {
  $qty = $stocks->$k;

  $stats = $v['stats'];
  $quote = $v['quote'];
  
  $price = $quote["latestPrice"];
  $amount = number_format($price * $qty, 2);
  $total += $price * $qty;
  $price = number_format($price, 2);
  $qty = number_format($qty, 0);
  $change = $quote["change"];
  $changePer = number_format($quote["changePercent"] * 100, 2);
  $latest = date("Y-m-d h:m", substr($quote["latestUpdate"],0,10));
  $day200 = $stats["day200MovingAvg"];

  $tbl .= <<<EOF
<tr><td>$k</td><td>$price</td><td>$day200</td><td>$qty<br>$amount</td><td>$change<br>$changePer</td><td>$latest</td></tr>
EOF;
}

$total = number_format($total, 2);
$tbl .=<<<EOF
</tbody>
</table>
<p>Total Amount: $total</p>
</div>
$footer
EOF;

echo $tbl;


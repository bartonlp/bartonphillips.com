<?php
// mutual.php
// Get the mutual fund info from Wall Street Journal
// Does a file_get_contents() POST

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_POST['mutual']) {
  $mutual = $_POST['mutual'];

  // Ues alphavantage.co as iex no longer seems to support my mutual funds.
  
  $alphakey = require("/var/www/bartonphillipsnet/PASSWORDS/alpha-token");
  $url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$mutual&apikey=$alphakey";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  $ret = curl_exec($ch);
  $ar = json_decode($ret, true);
  
  $date = $ar['Meta Data']['3. Last Refreshed'];
  $close = $ar['Time Series (Daily)'][$date]['4. close'];
  echo "$date:$close";
  exit;
};

$h->title = "Mutual Funds Prices";
$h->css =<<<EOF
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
EOF;

$h->banner = "<h1>Mutual Funds Info</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<hr>
<div>
EOF;

$mutual = "";
$stocks = new stdClass;

// BLP 2021-09-08 -- remove stocks from stocks.stocks. There is no stocks database.

$sql = "select stock, qty from stocks where status = 'mutual'";

$S->query($sql);

while([$stock, $qty] = $S->fetchrow("num")) {
  $mutual .= "$stock,";
  $stocks->$stock = $qty;
}

$mutual = rtrim($mutual, ',');

$tbl = <<<EOF
<table id='mutual'>
<thead>
<tr><th>Name</th><th>Price</th><th>Qty<br>Amount</th><th>latest</th></tr>
</thead>
<tbody>
EOF;

$total = 0;

foreach($stocks as $stock=>$qty) {
  $options = array('http' => array(
                                   'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                   'method'  => 'POST',
                                   'content' => http_build_query(array('mutual'=>$stock))
                                  )
                  );

  $context  = stream_context_create($options);

  // Now this is going to do a POST! This is in lieu of doing a Javascript function.
  // This way it is all php.

  $info = file_get_contents("https://www.bartonphillips.com/stocks/mutualiex.php", false, $context);

  preg_match("~(.*):(.*)~", $info, $m);
  $date = $m[1];
  $close = $m[2];
  //echo "$stock: close=$close<br>";
  $amount = number_format($close * $qty, 2);
  $total += $close * $qty;
  $price = number_format($close, 2);
  $qty = number_format($qty, 0);
  $latest = $date;

  $tbl .= <<<EOF
<tr><td>$stock</td><td>$price</td><td>$qty<br>$amount</td><td>$latest</td></tr>
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


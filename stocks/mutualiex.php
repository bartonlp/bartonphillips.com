<?php
// mutual.php
// Get the mutual fund info from Wall Street Journal
// Does a file_get_contents() POST
/*
CREATE TABLE `mutuals` (
  `date` date NOT NULL,
  `stock` varchar(100) NOT NULL,
  `price` varchar(30) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`date`,`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

function getmutualdata($mutual) {
  // Ues alphavantage.co as iex no longer seems to support my mutual funds.
  
  $alphakey = require("/var/www/PASSWORDS/alpha-token");
  // BLP 2022-11-19 - on Nov 4 they made the following an extra charge item. So I will use
  // GLOBAL_QUOTE instead.
  //$url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$mutual&apikey=$alphakey";
  //$date = $ar['Meta Data']['3. Last Refreshed'];
  //$close = $ar['Time Series (Daily)'][$date]['4. close'];

  $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$mutual&apikey=$alphakey";
  $ar = json_decode(file_get_contents($url), true);
  $close = $ar['Global Quote']['05. price'];
  $date = $ar['Global Quote']['07. latest trading day'];
  return [$date, $close];
}

if($_GET['page'] == "EndOfDay") {
  $S->query("select stock, qty from stocks where status = 'mutual'");

  while([$stock, $qty] = $S->fetchrow("num")) {
    [$date, $close] = getmutualdata($stock);
        
    $S->query("insert into mutuals (date, stock, price, qty, created, lasttime) values('$date', '$stock', '$close', '$qty', now(), now()) ".
              "on duplicate key update price='$close', qty='$qty', lasttime=now()");
  }
  echo "EndOfDay Done" . PHP_EOL;
  exit();
}
  
if($_POST['mutual']) {
  $mutual = $_POST['mutual'];

  [$date, $close] = getmutualdata($mutual);
  
  echo "$date:$close";
  exit;
};

$S>title = "Mutual Funds Prices";
$S->css =<<<EOF
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

$S->banner = "<h1>Mutual Funds Info</h1>";

[$top, $footer] = $S->getPageTopBottom();

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
  $close = (float)$m[2];
  $amount = number_format($close * $qty, 2);
  $total += $close * $qty;
  $price = number_format((float)$close, 2);
  $qty = number_format($qty, 0);
  $latest = $date;

  $tbl .= <<<EOF
<tr><td>$stock</td><td>$price</td><td>$qty<br>$amount</td><td>$latest</td></tr>
EOF;
}

$total = number_format($total, 2);
$tbl = <<<EOF
<table id='mutual'>
<thead>
<tr><th>Name</th><th>Price</th><th>Qty<br>Amount</th><th>latest</th></tr>
</thead>
<tbody>
$tbl
</tbody>
</table>
EOF;

echo <<<EOF
$top
<hr>
<div>
$tbl
<p>Total Amount: $total</p>
</div>
<hr>
$footer
EOF;

<?php
// stockvalue.php
// Check the stocks.values table which has the price*qty values for all my 'active' stocks.
// A lot more to do here.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);

$h->title = "Stock Values";
$h->banner = "<h1>Stock Values</h1>";
$h->css =<<<EOF
<style>
.per {
  color: red;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);
$sql = "select stock, price, qty from stocks.stocks where status='active'";
$S->query($sql);

$stocks = [];

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  if($stock == 'RDS-A') $stock = 'RDS.A';
  $stocks[$stock] = [$price, $qty];
}

$str = "https://api.iextrading.com/1.0/stock/market/batch?symbols=" . implode(',', array_keys($stocks)) . "&types=quote";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $str);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ret = curl_exec($ch);
$ar = json_decode($ret);

$total = 0.0;
$curval = 0.0;

foreach($stocks as $stk=>$val) {
  if($stk == 'RDS-A') $stk = "RDS.A";
  $S->query("select value from stocks.`values` where stock='$stk' order by date desc limit 100");
  
  $cnt = 0;

  $curval += $ar->$stk->quote->latestPrice * $val[1]; // latestPrice is 0 or undfined so 0.

  while(list($value) = $S->fetchrow('num')) {
    $total += $value;

    // These is one stock that is not NOT in iex! So get them from Yesterday's Alpha.
    
    if($cnt == 0 && $stk == "DDAIF") {
      //echo "$stk: $value<br>";
      $curval += $value;
    }
    ++$cnt;
  }
}
$av = $total / $cnt;
$percent = number_format(($curval - $av) / $av * 100, 2);
$curval = number_format($curval, 2);
$av = number_format($av, 2);
if($percent[0] == '-') {
  $percent = "<span class='per'>$percent</span>";
}
                         
echo <<<EOF
$top
Yesterday's Close: $$curval<br>
100 Day Moving Average: $$av<br>
Percent: $percent<br>
$footer
EOF;

/*
CREATE TABLE `values` (
  `date` date NOT NULL,
  `stock` varchar(10) NOT NULL,
  `value` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`date`,`stock`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
*/

<?php
// stockvalue.php
// Check the stocks.values table which has the price*qty values for all my 'active' stocks.
// A lot more to do here.
// BLP 2020-08-28 -- fixed for cloud.iexapis.com and changed printout to reflect current values and
// use iex stats to get a 200 day moving average. Remove the info from the stocks.`values` table.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);

$iex_token = require_once("/var/www/bartonphillipsnet/PASSWORDS/iex-token");
//$iex_token = file_get_contents("https://bartonphillips.net/PASSWORDS/iex-token.php");

$h->title = "Stock Values";
$h->banner = "<h1>Stock Values</h1>";
$h->css =<<<EOF
<style>
.per {
  color: red;
}
#info {
  text-align: center;
  width: 420px;
  padding: 5px;
  border: 5px solid black;
  margin: auto;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);
$sql = "select stock, price, qty from stocks where status in('active','mutual')";
$S->query($sql);

$stocks = [];

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  if($stock == 'RDS-A') $stock = 'RDS.A';
  $stocks[$stock] = [$price, $qty];
}

$str = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=" . implode(',', array_keys($stocks)) .
       "&types=quote,stats&filter=day200MovingAvg,latestPrice&token=$iex_token";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $str);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ret = curl_exec($ch);
$ar = json_decode($ret);
//vardump("ar", $ar);
$av = 0.0;
$curval = 0.0;

foreach($stocks as $stk=>$val) {
  // val[1] is qty.
  
  $curval += $ar->$stk->quote->latestPrice * $val[1];
  $av += $ar->$stk->stats->day200MovingAvg * $val[1];
}
$percent = number_format(($curval - $av) / $av * 100, 2);
$curval = number_format($curval, 2);
$av = number_format($av, 2);
if($percent[0] == '-') {
  $percent = "<span class='per'>$percent</span>";
}
                         
echo <<<EOF
$top
<div id="info">
Current value: $$curval<br>
200 Day Moving Average: $$av<br>
Percent current to moving average: $percent%<br>
</div>
$footer
EOF;

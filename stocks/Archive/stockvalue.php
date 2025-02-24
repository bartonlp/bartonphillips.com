<?php
// stockvalue.php
// Check the stocks.values table which has the price*qty values for all my 'active' stocks.
// A lot more to do here.
// BLP 2020-08-28 -- fixed for cloud.iexapis.com and changed printout to reflect current values and
// use iex stats to get a 200 day moving average. Remove the info from the stocks.`values` table.

exit("<h1>cloud.ipxapis.com No Longer Has a Free Subscription</h1>");

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$iex_token = require_once("/var/www/PASSWORDS/iex-token");

$S->title = "Stock Values";
$S->banner = "<h1>Stock Values</h1>";
$S->css =<<<EOF
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
EOF;

[$top, $footer] = $S->getPageTopBottom();
$sql = "select stock, price, qty from stocks where status in('active','mutual')";
$S->sql($sql);

$stocks = [];

while(list($stock, $price, $qty) = $S->fetchrow('num')) {
  $stocks[$stock] = [$price, $qty];
}

$str = "https://cloud.iexapis.com/stable/stock/market/batch?symbols=" . implode(',', array_keys($stocks)) .
       "&types=quote,stats&filter=day200MovingAvg,latestPrice&token=$iex_token";

$ar = json_decode(file_get_contents($str), true);

/*
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $str);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ret = curl_exec($ch);
$ar = json_decode($ret);
*/

$av = 0.0;
$curval = 0.0;
//vardump("ar", $ar);

foreach($stocks as $stk=>$val) {
  // val[1] is qty.

  $curval += $ar[$stk]["quote"]["latestPrice"] * $val[1];
  $av += $ar[$stk]["stats"]["day200MovingAvg"] * $val[1];
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

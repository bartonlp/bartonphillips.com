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

$S->query("select stock from stocks.stocks where status='active'");
$r = $S->getResult();

$total = 0.0;
$curval = 0.0;

while(list($stk) = $S->fetchrow($r, 'num')) {
  if($stk == 'RDS-A') $stk = "RDS.A";
  
  //echo "$stk<br>";
  
  $S->query("select value from stocks.`values` where stock='$stk' order by date desc limit 100");
  
  $cnt = 0;
  
  while(list($value) = $S->fetchrow('num')) {
    if($cnt == 0) {
      $curval += $value;
      error_log("stock: $stk, value: $value");
    }
    
    $total += $value;
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

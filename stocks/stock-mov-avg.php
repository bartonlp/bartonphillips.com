<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$h->banner = "<h1>Stock Info</h1>";
$h->css = <<<EOF
<style>
td { padding: 5px; text-align: right; }
</style>
EOF;

[$top, $footer] = $S->getPageTopBottom($h);

$totalAmt = 0;
$hi = 0;
$low = 9999999999;

$S->query("select * from stocktotals order by created");

for($cnt=0; ([$total, $created] = $S->fetchrow("num")); ++$cnt) {
  $stocks .=  "<tr><td>$total</td><td>$created</td></tr>";
  $total = preg_replace("~,~", "", $total);
  $hi = ($hi < $total) ? $total : $hi;
  $low = ($low > $total) ? $total : $low;
  $ar[] = $total;
}

// Only look at the last 200 days or less.

for($i = $cnt -1; $i > $cnt - 201; --$i) {
  $totalAmt += $ar[$i];
  if($i == 0) break;
}

$hi = number_format($hi, 2);
$low = number_format($low, 2);
//$amt = number_format($totalAmt, 2);
$start = number_format($ar[0], 2);
$end = number_format($ar[$cnt -1], 2);
$diff = number_format($ar[$cnt -1] - $ar[0], 2);
$moving = number_format($totalAmt / $cnt, 2);

$per = number_format(($diff / $start * 100), 2);
if($per < 0) {
  $per = "<span style='color: red'>$per</span>";
}

echo <<<EOF
$top
<hr>
<table border="1">
<thead>
<tr><th>Amount</th><th>Date</th></tr>
</thead>
<tbody>
$stocks
</tbody>
</table>
<hr>
Number of days: $cnt<br>
Heighest value: $hi<br>
Lowest value: $low<br>
<!-- totalAmt=$amt<br> -->
Moving Avg for $cnt days: $moving<br>
From start ($start) to today ($end) difference is: $diff ($per%)<br>
<hr>
$footer
EOF;


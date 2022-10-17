<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$h->banner = "<h1>Stock Info</h1>";
$h->css = <<<EOF
<style>
td { padding: 5px; text-align: right; }
</style>
EOF;

$h->title = "Stock Info";
$h->banner = "<h1>$h->title</h1>";

[$top, $footer] = $S->getPageTopBottom($h);

$totalAmt = 0;

$hi = 0;
$low = 9999999999;

$S->query("select * from stocktotals order by created");

$djicnt = 0;
$djitotal = 0;

for($cnt=0; ([$total, $dji, $created] = $S->fetchrow("num")); ++$cnt) {
  if(!$dji) {
    $dji = '';
  } else {
    $tmp = preg_replace("~,~", "", $dji);
    $djitotal += $tmp;
    ++$djicnt;
    $lastdji = $tmp;
  }
  $stocks .=  "<tr><td>$dji</td><td>$total</td><td>$created</td></tr>";
  $total = preg_replace("~,~", "", $total);
  $hi = ($hi < $total) ? $total : $hi;
  $low = ($low > $total) ? $total : $low;
  $ar[] = $total;
}

// Only look at the last 200 days or less.

$totalDji = 0;

for($i = $cnt -1; $i > $cnt - 201; --$i) {
  $totalAmt += $ar[$i];
  if($i == 0) break;
}

$hi = number_format($hi, 2);
$low = number_format($low, 2);
$end = number_format($ar[$cnt -1], 2); // Current formated amount
$diffTmp = $ar[$cnt -1] - $ar[0]; // DiffTmp unformated
$per = number_format(($diffTmp / $ar[0] * 100), 2); // Percent formated
$start = number_format($ar[0], 2); // Start formated
$diff = number_format($diffTmp, 2); // Diff formated
$moving = number_format($totalAmt / $cnt, 2);
$djimoving = number_format($djitotal / $djicnt, 2);
$lastdji = number_format($lastdji, 2);
if($per < 0) {
  $per = "<span style='color: red'>$per</span>";
}

$dowHigh = 36799.65;
$dowCur = preg_replace("~,~", '', $lastdji);
$sign = ($dowCur < $dowHigh) ? '-' : '';
$upDown = ($sign == '-') ? "down " : "up ";

$dowDiff = $upDown . number_format(($dowTmpDiff = $dowHigh - $dowCur), 2);
$dowDiffPer = $sign . number_format(($dowTmpDiff / $dowHigh) * 100) . "%";

echo <<<EOF
$top
<hr>
<table border="1">
<thead>
<tr><th>DJI</th><th>Amount</th><th>Date</th></tr>
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
From start on Feb 2, 2022 ($start) to today ($end) difference is: $diff ($per%)<br>
Last all-time high on Jan 4, 2022 of $36,799.65. Current Dji is $lastdji, Dji Moving Average for $djicnt days: $djimoving<br>
From the last high in Jan 4, 2022 to the current price for the DOW is $dowDiff or $dowDiffPer.
<hr>
$footer
EOF;


<?php
// mutual.php
// Get the mutual fund info from Wall Street Journal
// Does a file_get_contents() POST

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);
use PHPHtmlParser\Dom;

if($_POST['mutual']) {
  $mutual = $_POST['mutual'];
  
  $dom = new Dom;
  $dom->loadFromUrl("http://quotes.wsj.com/mutualfund/$mutual/historical-prices");
  $quote = $dom->find("#quote_val")->text;
  $change = $dom->find("#quote_change")->text;
  $changePercent = $dom->find("#quote_changePer")->text;
  //$quoteDate = $dom->find("#quote_dateTime")->text;
  
  $ret = json_encode(array('stocks'=>$mutual,
                           'quote'=>$quote,
                           'change'=>$change,
                           'per'=>$changePercent
                          )
                    );
  echo $ret;
  exit();
}

$sql = "select stock, qty from stocks.stocks where status = 'mutual'";

$S->query($sql);

$tbl = <<<EOF
<table id='mutual'>
<thead>
<tr><th>Name</th><th>Quote</th><th>$</th><th>Change</th><th>%</th></tr>
</thead>
<tbody>
EOF;

$total = 0;

while(list($stock, $qty) = $S->fetchrow("num")) {
  $stock = preg_replace("/-BLP/", '', $stock);

  $options = array('http' => array(
                                 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                 'method'  => 'POST',
                                 'content' => http_build_query(array('mutual'=>"$stock"))
                                )
                );

  $context  = stream_context_create($options);

  // Now this is going to do a POST!

  $info = file_get_contents("https://www.bartonphillips.com/examples/stockquotes/mutual.php",
                            false, $context);
  $i = json_decode($info);
  $price = $i->quote;
  $total += $price * $qty;
  $dol = number_format($price * $qty, 2);
  
  $tbl .= "<tr><td>$i->stocks</td><td>$price</td><td>$dol</td><td>$i->change</td><td>$i->per</td></tr>";
}

$tbl .=<<<EOF
</tbody>
</table>
EOF;

$total = number_format($total, 2);

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
$tbl
</div>
<h4>Total: $total</h4>
<hr>
$footer
EOF;

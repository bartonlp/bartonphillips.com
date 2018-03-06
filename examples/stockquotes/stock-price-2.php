<?php
// stock-price-2.php
// Uses stock-price-2.js and one of the websocket servers: stock-websocket.js or
// stock-websocket-sync.js. Both are nodejs programs.
// Also uses PHPHtmlParser\Dom to scrape the Wall Street Journal site for the DJIA

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

use PHPHtmlParser\Dom;

if($_GET['page'] == 'dow') {
  $dom = new Dom;
  $dom->loadFromUrl('http://quotes.wsj.com/index/DJIA');
  $dji = $dom->find("#quote_val")->text;
  $change = $dom->find("#quote_change")->text;

  $ret = json_encode(array("dji"=>$dji, "change"=>$change));
  echo $ret;
  exit();
}

$h->title = "Updating Stock Quotes";
$h->css =<<<EOF
<style>
td {
  padding: .3rem;
  text-align: right;
}
th {
  padding: .3rem;
}
</style>
EOF;

$h->banner = "<h1>Stock Prices</h1>";

$b->script = "<script src='./stock-price-2.js'></script>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<hr>
<div id='dji'></div>
<div id='stock-data'></div>
$footer
EOF;

<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

require "./StockApi.php";

echo "START<br>";
echo str_repeat(' ', 4096);
ob_flush();
flush();

$stock = new StocksApi($S);
$result = $stock->getQuotes();
echo "<table border='1'>$result->rows</table>";

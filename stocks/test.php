<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

require "./StockApi.php";

echo "Start<br>";
$stock = new StocksApi($S);
$data = $stock->getQuotes();
echo "<table border='1'>$data->rows</table>";
echo "Done<br>";
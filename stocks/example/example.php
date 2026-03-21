<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);
require_once "StockApi.php";
require_once "StockQuoteDTO.php";
require_once "StockOverviewDTO.php";
require_once "StockDataDTO.php";

$S = new SiteClass($_site);

$stock = new StocksApi($S);

$stockData = $stock->getQuotes();
vardump("stockData", $stockData);

header('Content-Type: application/json');
echo json_encode($stockData, JSON_PRETTY_PRINT);

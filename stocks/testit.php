<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$alphaKey = require '/var/www/PASSWORDS/alpha-prem-token';
$url = "https://www.alphavantage.co/query?function=:function&symbol=:stock&apikey=$alphaKey";

$urlTmp = str_replace(':stock', 'DIA', $url);

$url = str_replace(':function', 'GLOBAL_QUOTE', $urlTmp);
vardump("url", $url);
$json = file_get_contents($url);
$data = json_decode($json, true);
vardump("data", $data);
$price = $data['Global Quote']['05. price'] ?? 0;
$high = $data['Global Quote']['03. high'] ?? 0;
$low = $data['Global Quote']['04. low'] ?? 0;

echo "price=$price, high=$high, low=$low<br>";

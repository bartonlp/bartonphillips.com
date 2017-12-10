<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

$stock = $_GET['stock'];

$alphakey = "FLT73FUPI9QZ512V";
$str = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";

$h = curl_init();
curl_setopt($h, CURLOPT_URL, $str);
curl_setopt($h, CURLOPT_HEADER, 0);
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

$alpha = curl_exec($h);
$alpha = json_decode($alpha, true); // decode as an array

vardump($alpha);

$prefix = "https://api.iextrading.com/1.0";
$str = "$prefix/stock/market/batch?symbols=$stock&types=quote";

$h = curl_init();
curl_setopt($h, CURLOPT_URL, $str);
curl_setopt($h, CURLOPT_HEADER, 0);
curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

// We get the stocks from the array above  
$ret = curl_exec($h);
$ar = json_decode($ret);
vardump("ar", $ar);

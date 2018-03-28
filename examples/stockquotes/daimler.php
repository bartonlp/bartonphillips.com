<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);
use PHPHtmlParser\Dom;

date_default_timezone_set("America/New_York");

$dom = new Dom;
$dom->loadFromUrl("http://quotes.wsj.com/DDAIF");
$quote = $dom->find("#quote_val")->text;
$change = $dom->find("#quote_change")->text;
$changePercent = $dom->find("#quote_changePer")->text;
$quoteDate = $dom->find("#quote_dateTime")->text;
echo "$quoteDate<br>";

preg_match("~(\d{1,2}:\d{2}) (..) (...) (\d{2})/(\d{2})/(\d{2})~", $quoteDate, $m);
$str = "20$m[6]-$m[4]-$m[5] $m[1] $m[2]";
echo date("Y-m-d H:i T", strtotime($str)) . "<br>";
echo "quote: $quote, change: $change, %: $changePercent<br>";

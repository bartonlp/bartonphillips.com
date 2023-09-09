<?php
require("./vendor/autoload.php");
use Jaybizzle\CrawlerDetect\CrawlerDetect;

$CrawlerDetect = new CrawlerDetect;

// Check the user agent of the current 'visitor'
if($CrawlerDetect->isCrawler()) {
  echo "NAME: {$_SERVER['REMOTE_HOST']}<br>";
  // true if crawler user agent detected
}

// Pass a user agent as a string
//if($CrawlerDetect->isCrawler('Mozilla/5.0 (compatible; Sosospider/2.0;
//+http://help.soso.com/webspider.htm)')) {
if($CrawlerDetect->isCrawler('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 +http://bartonphillips.com)')) {
  echo "SPIDER<br>";
  // true if crawler user agent detected
}

// Output the name of the bot that matched (if any)
echo $CrawlerDetect->getMatches();
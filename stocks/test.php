<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);
use PHPHtmlParser\Dom;

$dom = new Dom;

$dom->loadFromUrl('https://www.marketwatch.com/investing/index/djia');
/*
$quoteDate = $dom->find(".timestamp__time bg-quote")->text();
$dji = $dom->find(".intraday__data .value")->text();
$change = $dom->find(".intraday__data .change--point--q bg-quote")->innerHTML;
$changePercent = $dom->find(".intraday__data .change--percent--q bg-quote")->innerHTML;

echo "date: $quoteDate, dji: $dji, change: $change, percent: $changePercent<br>";
*/
$group = $dom->find(".markets__group");

$dow = $group->find(".price bg-quote")->innerHTML;
$ch = $group->find(".change bg-quote")->innerHTML;
$per = $group->find(".percent bg-quote")->innerHTML;
echo "dow, $dow, ch: $ch, per: $per<br>";

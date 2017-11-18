<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$sql = "select stock, max(lasttime) as date from stocks.pricedata group by stock";
$S->query($sql);
while($row = $S->fetchrow('assoc')) {
  vardump($row);
}

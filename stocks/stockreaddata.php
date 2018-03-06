<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

function checkUser($S) {
  if($userId = $_COOKIE['SiteId']) {
    $sql = "select name from members where id=$userId";

    if($n = $S->query($sql)) {
      list($memberName) = $S->fetchrow('num');
      if($memberName != "Barton Phillips") {
        echo "<h1>Go Away</h1>";
        exit();
      }
    }
  } else {
    echo "<h1>Go Away</h1>";
    exit();
  }
};

checkUser($S);

$sql = "select stock, max(lasttime) as date from stocks.pricedata group by stock";
$S->query($sql);
while($row = $S->fetchrow('assoc')) {
  vardump($row);
}

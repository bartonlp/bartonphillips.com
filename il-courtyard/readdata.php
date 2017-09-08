<?php
// il-courtyard
// Display Independent Living Suggestions

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

// Get the last tue of the month. That is when the CHEF's meeting is.
$edate = new DateTime("last tue of ", new DateTimeZone("America/New_York"));
//$sd = new DateTime();
$sdate = (new DateTime())->sub(new DateInterval("P1M"));
$sdate = new DateTime("last tue of " . $sdate->format("Y-m-d"));

$edd = $edate->format("Y-m-d");
$sdd = $sdate->format("Y-m-d");
echo "Information from $sdd to $edd<br><br>";

try {
  if($S->query("select * from courtyard where date between '$sdd' and '$edd' order by date")) {
    while(list($d, $date, $name, $apt, $comment, $email) = $S->fetchrow('num')) {
      echo "$date<br>$name - $apt<br>$comment<br>$email<br><br>";
    }
  } else {
    echo "No Data Found<br>";
  }  
} catch(Exception $e) {
  if($e->getCode() == 1146) {
    echo "Table Not Created Yet<br>";
    exit();
  }
  echo $e->getMessage() . "<br>";
  throw new Exception("Something Else is Wrong");
}

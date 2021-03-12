<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

$id = $_COOKIE['SiteId'];
//$ref = $_SERVER['SERVER_NAME'];

if($S->setSiteCookie('SiteId', "$id", date('U') - 3600, '/') === false) {
  echo "Can't set cookie in register.php<br>";
}

/*if(setcookie("SiteId", "111", (time() - 3600), "/", $ref) === false) {
  echo "We failed<br>";
} */
echo "Hello<br>";
echo $_SERVER['REMOTE_ADDR'] ."<br>";
echo "id: $id<br>";
echo "unset<br>";
echo "Done<br>";

<?php
// Check if this is an http or https connection

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_SERVER['HTTPS']) {
  echo "Great its HTTPS: " .$_SERVER['HTTPS'] . "<br>";
} else {
  echo "Only HTTP<br>";
}

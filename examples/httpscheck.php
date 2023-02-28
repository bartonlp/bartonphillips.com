<?php
// Check if this is an http or https connection

if($_SERVER['HTTPS']) {
  echo "Great its HTTPS: " .$_SERVER['HTTPS'] . "<br>";
} else {
  echo "Only HTTP<br>";
}

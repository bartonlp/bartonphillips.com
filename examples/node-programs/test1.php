<?php
if($name) {
  $value = "<p>name=$name</p>";
}
if($test && $name) {
  $value = "<p>name=$name, test=$test</p>";
}
echo "<h1>This is test1.php</h1>$value";

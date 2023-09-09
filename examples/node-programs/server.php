<?php
// PHP is actually Much simpler than nodejs.

$ip = $_SERVER['REMOTE_ADDR'];
$agent = $_SERVER['HTTP_USER_AGENT'];
$ref = $_SERVER['HTTP_REFERER'];

if($_GET) {
  $name = $_GET['name'];
  $test = $_GET['test'];
  error_log("server.php GET: ip=$ip, agent=$agent, ref=$ref, name=$name, test=$test");
  echo "<p style='font-size: 40px'>Server.php GET says: <span style='color: white;background: green; padding: 0 8px; border-radius: 10px'>$name, $test</span></p>";
  exit();
}
if($_POST) {
  $name = $_POST['name'];
  $test = $_POST['test'];
  error_log("server.php POST: ip=$ip, agent=$agent, ref=$ref, name=$name, test=$test");
  echo "<p style='font-size: 40px'>Server.php POST says: <span style='color: red'>$name $test</span></p>";
  exit();
}

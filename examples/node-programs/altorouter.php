<?php
// For this to run under 'apache' 
// we would need to edit our '.htaccess' file and add:
//   RewriteEngine on
//   RewriteCond %{REQUEST_FILENAME} !-f
//   RewriteRule . altorouter.php [L]
// Which means everything will go through 'altorouts.php' that is not a file or directory.
// 'composer require altorouter/altorouter'
// https://github.com/dannyvankooten/AltoRouter
// 'composer require pug-php/pug:^3.0'
// https://github.com/pug-php/pug

// This file DOES NOT DO a require_onece(getenv("SITELOADNAME") or set $S.
// The $router->map() functions can do it to set up $_site.

require_once("/var/www/vendor/autoload.php");

$router = new AltoRouter();

// Do Routing

$router->setBasePath('examples/node-programs/'); // This needs the trailing /

$router->map('GET', '/', function() {
  require('start.php');
});

$router->map('GET', '/client', function() {
  require('client.php');
});

$router->map('GET', '/clienthtml', function() {
  require('client.html');
});

$router->map('GET', '/client-for-node-server', function() {
  require('client-for-node-server.php');
});

$router->map('GET', '/get', function() {
  require('test1.php');
});

$router->map('GET', '/get/[*:name]/[*:test]', function($x) {
  $name = $x['name'];
  $test = $x['test'];
  require('test1.php');
});

$router->map('GET', '/fast/[i:a]', function($x) {
  $name = $x['a'];
  require('test1.php');
});

$router->map('POST', '/gotoit', function() {
  $name = $_POST['name'];
  $test = $_POST['test'];
  echo "gotoit POST: name=$name, test=$test";
});

$router->map('POST', '/gotoit/[*:name]/[*:test]', function($x) {
  $one = $_POST['one'];
  $two = $_POST['two'];
  $name = $x["name"];
  $test = $x["test"];
  echo "gotoit POST: name=$name, test=$test, one=$one, two=$two<br>";
});

$match = $router->match();

if(is_array($match) && is_callable($match['target'])) {
  call_user_func( $match['target'], $match['params'] );
  exit();
} elseif($match) {
  header("location: {$match['target']}");
  exit();
} else {
  //header("HTTP/1.0 404 Not Found");
  //echo "<h1>Sorry, what you were looking for we could not find: 404 Not Found</h1>";
  // or
  require("404.php");
}


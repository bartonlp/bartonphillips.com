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

error_log("altorouter START: ".print_r($_GET, true));
error_log("altorouter START: ".print_r($_POST, true));

require_once("/var/www/vendor/autoload.php");
//$_site = require_once(getenv("SITELOADNAME"));

$router = new AltoRouter();

// Do Routing

$router->setBasePath('/examples/node-programs');

error_log("altorouter.php router: ".print_r($router, true));

$router->map('GET', '/get/', 'test1.php', 'home');

$router->map('GET', '/get/[*:name]/[*:test]', 'test2.php');

$router->map('GET', '/fast/[i:a]/', 'test.php', 'fast');

$router->map('POST', '/gotoit/', function() {
  error_log("altorouter.php in post");
  exit();
}, "Bog");
error_log("altorouter.php router: ".print_r($router, true));

$match = $router->match();

if(is_array($match) && is_callable($match['target'])) {
  call_user_func_array( $match['target'], $match['params'] );
  exit();
} elseif($match) {
  header("location: {$match['target']}");
  exit();
} else {
  header("HTTP/1.0 404 Not Found");
  require("404.php");
  exit();
}

echo "AT THE END";

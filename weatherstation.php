<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$key = require '/var/www/PASSWORDS/WeatherUnderGround-key';

if(($json = file_get_contents("https://api.weather.com/v2/pws/observations/current?stationId=KNCNEWBE345&format=json&units=e&apiKey=$key")) === false) exit("weather failed");

$info = json_decode($json);
vardump("info", $info);

if(($json = file_get_contents("https://api.weather.com/v2/pws/observations/all/1day?stationId=KNCNEWBE345&format=json&units=e&apiKey=$key")) === false) exit("weather failed");

$info = json_decode($json);
vardump("info", $info);
$info = json_decode($json);
vardump("info", $info);

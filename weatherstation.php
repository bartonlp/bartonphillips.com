<?php
// This queries the WeatherUndergound API for information.

$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$key = require '/var/www/PASSWORDS/WeatherUnderGround-key';
echo "key=$key<br>";

if(($json = file_get_contents("https://api.weather.com/v2/pws/observations/current?stationId=KNCNEWBE287&format=json&units=e&apiKey=$key")) === false) exit("weather failed");

$info = json_decode($json);
$neighborhood = $info->observations[0]->neighborhood;

vardump("info current", $info);

if(($json = file_get_contents("https://api.weather.com/v2/pws/observations/all/1day?stationId=KNCNEWBE287&format=json&units=e&apiKey=$key")) === false) exit("weather failed");

$info = json_decode($json);
vardump("info All $neighborhood", $info);


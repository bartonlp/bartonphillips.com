<?php
require("./isabot.class.php");

$robots = file_get_contents("robots.txt");
echo "$robots<br>";
//$what = "Mozilla/5.0 (compatible; woorankreview/2.0; +https://www.woorank.com/)";
$isabot = new IsABot('BartonTest', 1);
echo ($isabot->isBot($what) ? "BOT" : "NOT BOT") . "<br>";


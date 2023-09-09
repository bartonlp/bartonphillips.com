<?php
require("./isabot.class.php");

$robots = file_get_contents("Sitemap.xml");
echo "$robots<br>";
//$what = "Mozilla/5.0 (compatible; woorankreview/2.0; +https://www.woorank.com/)";
$isabot = new IsABot('BartonTest', 2);
echo ($isabot->isBot($what) ? "BOT" : "NOT BOT") . "<br>";


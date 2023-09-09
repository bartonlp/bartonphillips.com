<?php
require("./isabot.class.php");

// For testing

$isabot = new IsABot("Bartonlp");

//$what = $_SERVER['HTTP_USER_AGENT'];
//$what = "Mozilla/5.0 (compatible; woorankreview/2.0; +https://www.woorank.com/)";
$what = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 +http://bartonphillips.com)';
$bot = $isabot->isBot("$what");
$type = $bot[0] ? "Bot" : "Not Bot";

// Test it out

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<title>Test Is A Bot</title>
</head>
<body>
<h1>Test Is a Bot</h1>
<p id="result">$type $bot[1]</p>
</body>
</html>
EOF;

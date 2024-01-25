<?php
require("./isabot.class.php");

// For testing

$isabot = new IsABot("Bartonlp");

//$what = $_SERVER['HTTP_USER_AGENT'];
$what = "Mozilla/5.0 (compatible; woorankreview/2.0; +https://www.woorank.com/)"; $isabot->ip = "123.456.789.2";

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
$what
<p id="result">$type $bot[1]</p>
</body>
</html>
EOF;

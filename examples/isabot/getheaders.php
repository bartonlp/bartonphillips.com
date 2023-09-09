<?php
//header("Accept-CH: sec-ch-full-version-list");

$headers = apache_request_headers();

$tmp = $_SERVER['REMOTE_ADDR'] . "\n";
foreach ($headers as $header => $value) {
  $tmp .= "$header: $value\n";
}

$tmp .= "\n";

file_put_contents("data/headers", $tmp, FILE_APPEND);

echo <<<EOF
<!DOCTYPE html>
<html>
<!--<meta http-equiv="Accept-CH" content="Viewport-Width, Width" />-->
<head>
</head>
<body>
<h1>Test</h1>
<img src="https://bartonphillips.net/images/blp-image.png">
</body>
</html>
EOF;


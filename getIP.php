<?php
$ip = $_SERVER['REMOTE_ADDR'];
echo <<<EOF
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Your IP Address is: $ip</h1>
</body>
</html>
EOF;


<?php
//$rand = base64_encode(rand());
$rand = base64_encode(bin2hex(openssl_random_pseudo_bytes(8)));

//header('Reporting-Endpoints: default="https://bartonphillips.com/examples.js/cspreport.php"');

header("Content-Security-Policy: default-src 'self' https://bartonlp.com https://bartonphillips.net https://code.jquery.com; ".
       "img-src 'self' data: https://bartonphillips.net; ".
       "script-src 'nonce-$rand' 'unsafe-eval' 'strict-dynamic'; ".
       "report-uri https://bartonphillips.com/examples.js/cspreport.php", false);

echo "strlen rand=". strlen($rand). "<br>";

$ip = $_SERVER['REMOTE_ADDR'];

$request = apache_request_headers();
$response = get_headers("https://" . $request['Host']);

//error_log("examples.js/exampleAjax.php, ip=$ip, request: ". print_r($request, true). ", response: ". print_r($response, true));

$image = "data:image/png;base64," . base64_encode(file_get_contents("/var/www/bartonphillipsnet/images/146624.png"));
//echo "image: $image<br>";

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<title>csp test</title>
<link rel='shortcut icon' href="https://bartonphillips.net/images/favicon.ico">
<script nonce="$rand" src="https://code.jquery.com/jquery.js"></script>
<script nonce="$rand" src="https://bartonphillips.net/js/phpdate.js"></script>
</head>
<body>
<h1>CSP TEST</h1>
rand=$rand<br>
<div id="date"></div>
<img src="https://bartonphillips.net/images/146624.png"><br>
<img src="$image"><br>
<script nonce="$rand" src="https://code.jquery.com/jquery.min.js"></script>
<script nonce="$rand" src="https://bartonphillips.net/js/phpdate.js"></script>
<script nonce="$rand">
  let dateStr = phpdate("Y-m-d");
  console.log('test this thing. You know. date: ', dateStr);
  $("#date").html(`This is today's date: \${dateStr}`);
</script>
</body>
</html>
EOF;

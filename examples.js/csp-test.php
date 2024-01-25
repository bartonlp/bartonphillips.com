<?php
// This is a test of the Content-Security-Polocy.
// This program uses 'cspreport.php' program.

$rand = base64_encode(bin2hex(openssl_random_pseudo_bytes(8)));

//header('Reporting-Endpoints: default="https://bartonphillips.com/examples.js/cspreport.php"');

header("Content-Security-Policy: default-src 'self' https://bartonlp.com https://bartonphillips.net https://code.jquery.com; ".
       "img-src 'self' data: https://bartonphillips.net; ".
       "script-src 'nonce-$rand' 'unsafe-eval' 'strict-dynamic'; ".
       "report-uri https://bartonphillips.com/examples.js/cspreport.php", false);

$len = "strlen rand=". strlen($rand). "<br>";

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
<p>This program uses csp-report.php.</p>
rand=$rand<br>
$len
<div id="date"></div>
<p>Regular Image</p>
<img src="https://bartonphillips.net/images/146624.png"><br>
<p>base64 Image</p>
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

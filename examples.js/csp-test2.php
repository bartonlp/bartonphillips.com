<?php
// This is a test of the Content-Security-Polocy.
// The program can either be run without any arguments and will therefore generate NO errors,
// or it can be run as 'csp-test2.php?err=true' and it should generate an error via the
// 'cspreport.php' program. That program logs to 'error_log()'. See cspreport.php for details.

$rand = base64_encode(bin2hex(openssl_random_pseudo_bytes(8)));

if(!$_GET['err']) {
  $script = "script-src 'nonce-$rand' 'unsafe-eval' 'strict-dynamic';";
} else {
  $script = "script-src 'nonce-$rand';";
}
  
header("Content-Security-Policy-Report-Only: default-src 'self' https://bartonlp.com https://bartonphillips.net https://code.jquery.com; ".
       "img-src 'self' data: https://bartonphillips.net; ".
       $script .
       "report-uri https://bartonphillips.com/examples.js/cspreport.php");

$randlen = "strlen rand=". strlen($rand). "<br>";

$ip = $_SERVER['REMOTE_ADDR'];

$req = apache_request_headers();

$request = "<pre>apache_request_headers:\n" . print_r($req, true);
$response = "apache_response_headers:\n" . print_r(apache_response_headers(), true). "</pre>";

$image = "data:image/png;base64," . base64_encode(file_get_contents("/var/www/bartonphillipsnet/images/146624.png"));

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<title>csp test 2</title>
<link rel='shortcut icon' href="https://bartonphillips.net/images/favicon.ico">
<script nonce="$rand" src="https://code.jquery.com/jquery.js"></script>
<script nonce="$rand" src="https://bartonphillips.net/js/phpdate.js"></script>
</head>
<body>
<h1>CSP TEST</h1>
<p>This program uses 'Content-Security-Polocy'. I can be run either without a query or with one. If you enter the following query
the 'Content-Security-Polocy' logs infomation: '?err=true'.</p>
<hr>
rand=$rand<br>
$randlen
<div id="date"></div>
$request
$response
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

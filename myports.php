<?php
// BLP 2023-02-25 - use new approach

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->title = "Port #";
$S->banner = "<h1>SSH Port Numbers</h1>";
$S->css = "table { padding: 2px; } td { padding: 5px; }";

[$top, $footer] = $S->getPageTopBottom();

if($code = $_POST['code']) {
  if($code == "8653") {
    echo <<<EOF
$top
<hr>
<p>For the first three ssh entries:<br>
<code>ssh -p{port} barton@bartonphillips.org</code><br>
And the forth (server):<br>
<code>ssh -p2222 barton@bartonphillips.com</code></p>
<p>For the browsers enter in the location bar of the browser:<br>
<code>https://bartonphillips.org:{port}</code><br>
For port 8080 it needs to be <code>http://</code></p>
<table border="3">
<tr><td>Rpi ssh</td><td>4022</td></tr>
<tr><td>HP ssh</td><td>2022</td></tr>
<tr><td>Acer ssh</td><td>2023</td></tr>
<tr><td>Server ssh</td><td>2222</td></tr>
<tr><td>Browser HP Envy at /html</td><td>8020</td></tr>
<tr><td>Browser Rpi</td><td>8080</td></tr>
<tr><td>Browser HP Envy</td><td>8000</td></tr>
</table>
<hr>
$footer
EOF;
    exit();
  }
  echo "<h1>NOT AUTHORIZED</h1>";
  exit();
}

echo <<<EOF
$top
<hr>
<form method="post">
Secret Code: <input type="text" name="code" autofocus><br>
<button type="submit">Submit</button>
<form>
<hr>
$footer
EOF;


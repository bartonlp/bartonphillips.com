<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$h->title = "Port #";
$h->banner = "<h1>SSH Port Numbers</h1>";
$h->css = "table { padding: 2px; } td { padding: 5px; }";

[$top, $footer] = $S->getPageTopBottom($h);

if($code = $_POST['code']) {
  if($code == "8653") {
    echo <<<EOF
$top
<hr>
<table border="3">
<tr><td>Rpi</td><td>4022</td></tr>
<tr><td>HP</td><td>2022</td></tr>
<tr><td>Acer</td><td>2023</td></tr>
<tr><td>Server</td><td>2222</td></tr>
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


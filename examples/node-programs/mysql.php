<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$S->title = "Demo Program mysql.php";
$S->banner = "<h1>$S->title</h1>";
[$top, $footer] = $S->getPageTopBottom();

$res = print_r($results, true);

echo <<<EOF
$top
<hr>
<h1>This is mysql.php</h1>
<p>This is the ip=$ip</p>
<pre>$res</pre>
<hr>
$footer
EOF;
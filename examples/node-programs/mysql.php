<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$h->title = "Demo Program mysql.php";
$h->banner = "<h1>$h->title</h1>";
[$top, $footer] = $S->getPageTopBottom($h);

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
<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$h->title = "Demo Program what.php";
$h->banner = "<h1>$h->title</h1>";
[$top, $footer] = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>what.php: $id</h1>
$footer
EOF;

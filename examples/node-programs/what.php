<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$S->title = "Demo Program what.php";
$S->banner = "<h1>$S->title</h1>";
[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<h1>what.php: $id</h1>
$footer
EOF;

<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$S->title = "test";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<h1>test3.php</h1>
$footer
EOF;

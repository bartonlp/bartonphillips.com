<?php
$_site = require_once(getenv("SITELOAEDNAME"));
$S = new SiteClass($_site);
$__FILENAME = __FILE__;
require_once("addpage.php");

$h->title = "test";

[$top, $footer] = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>test3.php</h1>;
$footer
EOF;
<?php
// BLP 2023-02-25 - use new approach

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$S->banner = "<hr><h1 style='text-align: left'>Your IP Address is: $S->ip</h1>";

$S->nofooter = true;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
$footer
EOF;


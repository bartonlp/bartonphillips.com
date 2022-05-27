<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$h->banner = "<hr><h1 style='text-align: left'>Your IP Address is: $S->ip</h1>";

$b->nofooter = true;

[$top, $footer] = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
$footer
EOF;


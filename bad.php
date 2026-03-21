<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$x = 4 / 0;

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOL
$top
<p>Test</p>
$x
$bottom
EOL;

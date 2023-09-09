<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->noGeo = true;
$S->b_script = <<<EOF
<script type="module" src="https://bartonphillips.net/js/testgeo.js"></script>
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<h1>TEST</h1>
$footer
EOF;

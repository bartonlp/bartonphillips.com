<?php
// There are only four links to this file and they are:
// articles/javascript-siteclass.php
// articles/javascript-only.php
// articles/javascript-only-nojquery.php
// articles/javascript-only.js

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

[$top, $footer] = $S->getPageTopBottom();
echo <<<EOF
$top
$footer
EOF;

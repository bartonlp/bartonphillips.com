<?php
// There is only one link to this file and it is in javascript.
// articles/javascript-siteclass.php
// articles/javascript-only.php
// articles/javascript-only-nojquery.php

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

list($top, $footer) = $S->getPageTopBottom();
echo <<<EOF
$top
$footer
EOF;

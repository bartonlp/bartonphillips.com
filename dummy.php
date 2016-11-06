<?php
// There is only one link to this file and it is in javascript.

$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

list($top, $footer) = $S->getPageTopBottom();
echo <<<EOF
$top
$footer
EOF;

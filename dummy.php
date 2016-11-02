<?php
// There is only one link to this file and it is in javascript.

$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");
$S = new $_site['className']($_site); // takes an array if you want to change defaults

list($top, $footer) = $S->getPageTopBottom();
echo <<<EOF
$top
$footer
EOF;

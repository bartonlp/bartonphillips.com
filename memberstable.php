<?php
// Display the members table for bartonphillips

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$h->banner = "<h1>Member's table display</h1>";
$h->css = "<style>table { width: 100%; font-size: 10px; }</style>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

$T = new dbTables($S);

$sql = "select name, email, ip, agent, created, lasttime from members";

[$members] = $T->maketable($sql, array('attr'=>array('border'=>'1', 'id'=>'members')));

// ***************
// Render the page
// ***************

echo <<<EOF
$top
$members
$footer
EOF;


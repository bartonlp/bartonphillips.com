<?php
// g-test.php
// banner.css uses 'grid' for the header area. I could do this for GranbyRotary.
 
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);  
$S = new $_site->className($_site);

$h->banner = "<h1>Test</h1>";
$h->link =<<<EOF
  <link rel="stylesheet" href="./css/banner.css">
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top

$footer
EOF;

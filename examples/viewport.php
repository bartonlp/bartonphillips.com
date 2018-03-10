<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

$h->css = <<<EOF
  <style>
/* font-size: calc([minimum size] + ([maximum size] - [minimum size]) * ((100vw - [minimum
   viewport width]) / ([maximum viewport width] - [minimum viewport width]))); */

html {
  font-size: calc(14px + (26 - 14) * ((100vw - 300px) / (1600 - 300)));
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>This is a test</h1>
<p>This is text to show what is happending.
This is text to show what is happending.
This is text to show what is happending.
This is text to show what is happending.
This is text to show what is happending.</p>
$footer
EOF;

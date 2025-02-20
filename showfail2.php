<?php
// Show the modsecurity log file.

$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$logFile = '/var/log/fail2ban.log';

(($fh = fopen($logFile, 'r')) !== false) || exit(vardump("Error", error_get_last()));

while(($line = fgets($fh)) !== false) {
  $lines .= $line;
}

$lines = "<pre>$lines</pre>";

$S->title = "Show Fail2ban Log";
$S->banner = "<h1>$S->title</h1>";

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
$lines
<hr>
$bottom
EOF;

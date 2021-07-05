<?php
// Display the members table for bartonphillips

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$h->banner = "<h1>Member's table display</h1>";
$h->css = "<style>table { width: 100%; font-size: 10px; }</style>";

list($top, $footer) = $S->getPageTopBottom($h, $b);

$sql = "select name, email, ip, agent, created from members";
$S->query($sql);

$tbl = <<<EOF
<table border="1">
<thead>
<tr>
<th>Name</th><th>Email</th><th>IP</th><th>Agent</th><th>Created</th></tr>
</thead>
<tody>
EOF;

while([$name, $email, $ip, $agent, $created] = $S->fetchrow("num")) {
  $tbl .= "<tr><td>$name</td><td>$email</td><td>$ip</td><td>$agent</td><td>$created</td></tr>";
}

$tbl .= "</tbody>\n</table>\n";

// ***************
// Render the page
// ***************

echo <<<EOF
$top
$tbl
$footer
EOF;


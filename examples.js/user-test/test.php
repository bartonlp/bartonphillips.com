<?php
// Use two different databases. The mysitemap.json has dbinfo->usr=test and dbinfo->database=test.
// I use the original $_site to instantiate $X and then change dbinfo->user to be 'barton'.
// Now I instantiate $S. Now my masterdb works with the logagent table and I can still use the
// 'test' table in the 'test' database.
// I could have changed other things in $_site to count, countMe and noTrack to be OK for tracking
// etc.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$X = new Database($_site);
$_site->dbinfo->user = 'barton';
$_site->count = $_site->countMe = true;
$_site->noTrack = false;
$S = new $_site->className($_site);

$h->banner = "<h1>Test of two databases</h1>";
[$top, $footer] = $S->getPageTopBottom($h);

$S->query("select * from $S->masterdb.logagent limit 1");
$msg1 = "<pre>logagent: " . print_r($S->fetchrow('num'), true) . "</pre><br>";

$X->query("select * from test");
$msg2 = "<pre>test: " . print_r($X->fetchrow('num'), true) . "</pre></br>";

echo <<<EOF
$top
<div>$msg1</div>
<div>$msg2</div>
$footer
EOF;


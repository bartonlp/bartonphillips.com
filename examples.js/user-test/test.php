<?php
// Use two different databases. The mysitemap.json has dbinfo->user=test and dbinfo->database=test.
// I use the original $_site to instantiate $X and then change dbinfo->user to be 'barton'.
// Now I instantiate $S. Now my masterdb works with the logagent table and I can still use the
// 'test' table in the 'test' database.
// I could have changed other things in $_site to count and noTrack to be OK for tracking
// etc.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$_site->dbinfo->user = 'test'; // test user
$_site->dbinfo->database = "test"; // and test database
$_site->noTrack = true; // Set noTrack to true because the user is NOT barton. See Database CheckIfTablesExist().
$X = new Database($_site);

$S->banner = "<h1>Test of two databases</h1>";
[$top, $footer] = $S->getPageTopBottom();

$S->sql("select * from $S->masterdb.logagent where date(lasttime)=current_date() order by lasttime desc limit 1");
$msg1 = "<pre>logagent: " . print_r($S->fetchrow('assoc'), true) . "</pre><br>";

$X->sql("select * from test");
while($row = $X->fetchrow("assoc")) {
  $msg2 .= "<pre>test: " . print_r($X->fetchrow('assoc'), true) . "</pre></br>";
}
echo <<<EOF
$top
<p>The two databases have different 'user' values. SiteClass is using 'user' <i>barton</i>. Database is using 'user' <i>test</i>.</p>
<div>$msg1</div>
<div>$msg2</div>
$footer
EOF;


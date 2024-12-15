<?php
// Use two different databases. The mysitemap.json has dbinfo->user=barton and
// dbinfo->database=barton.
// I use the original $_site to instantiate $S and $X.
// Now I instantiate $S. Now my masterdb works with the logagent table and I can still use the
// 'test' table in the 'test' database.
// I could have changed other things in $_site to count and noTrack to be OK for tracking
// etc.

//exit("<h1>Not Authorized</h1>");

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$_site->dbinfo->user = 'barton'; // test user
$_site->dbinfo->database = "test"; // and test database
$_site->noTrack = true; // Set noTrack to true because the user is NOT barton. See Database CheckIfTablesExist().
$X = new Database($_site);

$S->banner = "<h1>Test of two databases</h1>";

[$top, $footer] = $S->getPageTopBottom();

$S->sql("select * from $S->masterdb.logagent where date(lasttime)=current_date() order by lasttime desc limit 1");
$msg1 = "<pre>logagent: " . print_r($S->fetchrow('assoc'), true) . "</pre><br>";

$X->sql("select * from $S->masterdb.test");

while($row = $X->fetchrow("assoc")) {
  foreach($row as $k=>$v) {
    $rows[] = "[$k] => $v";
  }
}

foreach($rows as $k=>$v) {
  $msg2 .= "    $v<br>";
  if(($k % 3) == 2) $msg2 .= " <br>";
}
$msg2 = rtrim($msg2, "<br>");
$msg2 = "<pre>test: Array<br>{<br>$msg2}</pre>";

echo <<<EOF
$top
<p>The two databases have the same user. One database is <b>barton</b> and the other is <b>test</b>.
I instantiate SiteClass, which uses user <b>barton</b> and database <b>barton</b>, into \$S
and the second database, <b>test</b>, into \$X.</p>
<p>I then use \$S to get the info from the <b>logagent</b> table for today, limit 1. I display it.
I then use \$X to get all of the info form the <b>test</b> database, table <b>test</b> and dispay it.</p>
<div>$msg1</div>
<div>$msg2</div>
$footer
EOF;


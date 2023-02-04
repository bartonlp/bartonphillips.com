<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);
$h = new stdClass;

$__FILENAME = __FILE__;
require_once("addpage.php");

$h->title = "Test for Server</title>";
$h->banner = "<h1>$h->title</h1>";
[$top, $footer] = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>This is a PHP demo that is loaded when we start at the location line in your browser with '/' or index.php.</p>
<hr>
<p>You should have the server running on <b>https://bartonphillips.com:3000</b>
  before running the test below.</p>
<a href="client-for-node-server.php">Test Node Server</a>
<hr>
<p>Unlike the <b>node js</b> server, the PHP server file is loaded by <b>Apache</b>
  automatically.</p>
<a href="client.php">Test PHP Server</a>
<hr>
$footer
EOF;

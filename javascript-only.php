<?php
// This has almost no html tags, it is all javascript

$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);
// Get the original php file
$file = file_get_contents("javascript-only.php");
// Clean up left and right arrows, $ \n and quotes. Make them all displayable.
$file = preg_replace(array("/</","/>/", "/\\$/", "/\n/", '/"/'), array("&lt;","&gt;", "\$", "<br>", "'"), $file);

echo <<<EOF
<!DOCTYPE html>
<html>
<body>
<!-- Because we use jQuery we need this first. If we used only javaScript we could do it all in the <script> tag. -->
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js'></script>
<!-- This does everything once we have jQurey -->
<script>
$("body").html("<style>"+
"pre {"+
"  font-size: .7em;"+
"  overflow: auto;"+
"  padding: .5em;"+
"  border-left: .5em solid gray;"+
"  background-color: #E5E5E5;"+
"}"+
"</style>"+
"<h1>Test of Java Only</h1>"+
"<a href='http://www.allnaturalcleaningcompany.com'>All Natural</a><br>"+
"<a href='dummy.php'>Dummy Not by anyone else</a><br>"+
"<p>PHP file that created this page:</p>"+
"<pre>$file</pre>"+
"<link rel='stylesheet' href='http://bartonphillips.net/css/blp.css'>"+
"<s"+"cript>var lastId = '$S->LAST_ID';</s"+"cript>"+
"<s"+"cript src='http://bartonphillips.net/js/fingerprint2.js'></s"+"cript>"+
"<s"+"cript src='http://bartonphillips.net/js/fingerprint.js'></s"+"cript>"+
"<s"+"cript src='http://bartonphillips.net/js/tracker.js'></s"+"cript>");
</script>
</body>
</html>
EOF;

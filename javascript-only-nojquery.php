<?php
// JavaScript Only no jQuery or SiteClass
// Get the original php file
$file = file_get_contents("javascript-only-nojquery.php");
// Clean up left and right arrows, $ \n and quotes. Make them all displayable.
$file = preg_replace(array("/</","/>/", "/\\$/", "/\n/", '/"/'), array("&lt;","&gt;", "\$", "<br>", "'"), $file);

echo <<<EOF
<!DOCTYPE html>
<body>
<script>
document.head.innerHTML = "<title>JavaScript Only</title>"+
    "<meta name=viewport content='width=device-width, initial-scale=1'>"+
    "<meta charset='utf-8'>"+
    "<meta name='copyright' content='2016 Barton L. Phillips'>"+
    "<meta name='Author' content='Barton L. Phillips, mailto:bartonphillips@gmail.com'>"+
    "<meta name='description' content='Bartonphillips'>"+
    "<link rel='stylesheet' href='http://bartonphillips.net/css/blp.css'>"+
    "<style>"+
    "pre {"+
    "  font-size: .7em;"+
    "  overflow: auto;"+
    "  padding: .5em;"+
    "  border-left: .5em solid gray;"+
    "  background-color: #E5E5E5;"+
    "}"+
    "</style>";

document.body.innerHTML = "<h1>Test of Java Only no jQuery or SiteClass</h1>"+
  "<a href='http://www.allnaturalcleaningcompany.com'>All Natural</a><br>"+
  "<a href='dummy.php'>Dummy</a><br><br>"+
  "<a href='javascript-siteclass.php'>JavaScript only plus jQuery and SiteClass</a><br>"+
  "<a href='proxy.php?javascript-only.php'>JavaScript only with jQuery and no SiteClass</a><br>"+
  "<p>PHP file that created this page:</p>"+
  "<pre>$file</pre>";
</script>
</body>
EOF;

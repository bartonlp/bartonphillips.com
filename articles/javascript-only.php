<?php
// JavaScript Only with jQuery but no SiteClass
// Get the original php file
$file = file_get_contents("javascript-only.php");
// Clean up left and right arrows, $ \n and quotes. Make them all displayable.
$file = preg_replace(array("/</", "/>/", "/\\$/", "/\n/", '/"/'), array("&lt;", "&gt;", "\$", "<br>", "'"), $file);

echo <<<EOF
<!DOCTYPE html>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js'></script>
<script>
jQuery(document).ready(function($) {
  $("").append("<html>");
  $("html").append("<head>");
  $("head").append("<title>JavaScript Only</title>"+
    "<meta name=viewport content='width=device-width, initial-scale=1'>"+
    "<meta charset='utf-8'>"+
    "<meta name='copyright' content='2016 Barton L. Phillips'>"+
    "<meta name='Author' content='Barton L. Phillips, mailto:bartonphillips@gmail.com'>"+
    "<meta name='description' content='Bartonphillips'>"+
    "<link rel='stylesheet' href='https://bartonphillips.net/css/blp.css'>"+
    "<style>"+
    "pre {"+
    "  font-size: .7em;"+
    "  overflow: auto;"+
    "  padding: .5em;"+
    "  border-left: .5em solid gray;"+
    "  background-color: #E5E5E5;"+
    "}"+
    "</style>"
  );
  $("script").remove();

  $("head").after("<body>");
  $("body").html("<h1>Test of Java Only jQuery no SiteClass</h1>"+
  "<a href='https://www.allnaturalcleaningcompany.com'>All Natural</a><br>"+
  "<a href='dummy.php'>Dummy</a><br><br>"+
  "<a href='javascript-siteclass.php'>JavaScript only plus jQuery and SiteClass</a><br>"+
  "<a href='proxy.php?javascript-only-nojquery.php'>JavaScript only no jQuery or SiteClass</a><br>"+
  "<p>PHP file that created this page:</p>"+
  "<pre>$file</pre>"
  );
});
</script>
EOF;

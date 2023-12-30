<?php
// JavaScript Only with jQuery but no SiteClass
// Get the original php file
$file = file_get_contents("javascript-only.php");
// Clean up left and right arrows, $ \n and quotes. Make them all displayable.
// * Note that when viewing the file the $ sign has a double back slash before it and \\n will
// * show as a line break so you will not see it.

$file = preg_replace(array("/</", "/>/", "/\\$/", "/\n/", '/"/'), array("&lt;", "&gt;", "\$", "<br>", "'"), $file);

echo <<<EOF
<!DOCTYPE html>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js'></script>
<script>
jQuery(document).ready(function($) {
  $("").append("<html>");
  $("html").append("<head>");
  $("head").append("<title>JavaScript with jQuery</title>"+
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
  $("body").html("<h1><i>javascript</i> and <i>jQuery</i> but no <i>SiteClass</i></h1>"+
  "<h3>This program does not have a fancy header or footer.</h3>"+
  "<a href='javascript-siteclass.php'>JavaScript only plus jQuery and SiteClass</a><br>"+
  "<a href='javascript-only-nojquery.html'>JavaScript only no jQuery or SiteClass</a><br>"+
  "<p>PHP file that created this page:</p>"+
  "<pre>$file</pre>"
  );
});
</script>
EOF;

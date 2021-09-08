<?php
// JavaScript Only with jQuery and SiteClass
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
// Get the original php file
$file = file_get_contents("javascript-siteclass.php");
// Clean up left and right arrows, $, \n and quotes. Make them all displayable.
// * Note that when viewing the file the $ sign has a double back slash before it and \\n will
// * show as a line break so you will not see it.

$file = preg_replace(array("/</", "/>/", "/\\$/", "/\n/", '/"/'), array("&lt;","&gt;", "\$", "<br>", "'"), $file);

$h->title = "JavaScript+jQuery+SiteClass";
$h->css =<<<EOF
  <style>
pre {
  font-size: .7em;
  overflow: auto;
  padding: .5em;
  border-left: .5em solid gray;
  background-color: #E5E5E5;
}
  </style>
EOF;

$h->script=<<<EOF
  <script>
jQuery(document).ready(function($) {
  $("head").after("<body>");
  $("<hr><h1><i>javaScript</i> with <i>jQuery</i> and <i>SiteClass</i></h1>"+
    "<h3>This program uses jQuery and has a nice header and footer due to SiteClass.</h3>" +
    "This a another link as an example: <a href='https://www.allnaturalcleaningcompany.com'>All Natural</a><br>"+
    "And another: <a href='dummy.php'>Dummy</a><br><br>"+
    "<a href='javascript-only-nojquery.html'>JavaScript only no jQuery or SiteClass</a><br>"+
    "<a href='javascript-only.php'>JavaScript only with jQuery and no SiteClass</a><br>"+
    "<p>PHP file that created this page:</p>"+
    "<pre>$file</pre>"
  ).insertBefore('footer');
  $("script").remove(); // REMOVE ALL script tags
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
$footer
EOF;

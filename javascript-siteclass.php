<?php
// JavaScript Only with jQuery and SiteClass
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);
// Get the original php file
$file = file_get_contents("javascript-siteclass.php");
// Clean up left and right arrows, $, \\n and quotes. Note the forth argument to the first array
// is \\n which you can't see.
// Make them all displayable.
$file = preg_replace(array("/</", "/>/", "/\\$/", "/\n/", '/"/'),
                     array("&lt;","&gt;", "\$", "<br>", "'"), $file);

$h->title = "JavaScriptOnly";
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
  $("<h1>JavaScript Only with jQuery and SiteClass</h1>"+
    "<a href='http://www.allnaturalcleaningcompany.com'>All Natural</a><br>"+
    "<a href='dummy.php'>Dummy</a><br><br>"+
    "<a href='proxy.php?javascript-only-nojquery.php'>JavaScript only no jQuery or SiteClass</a><br>"+
    "<a href='proxy.php?javascript-only.php'>JavaScript only with jQuery and no SiteClass</a><br>"+
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

<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$referer = $_SERVER['HTTP_REFERER'];

if(!preg_match("/bartonphillips\.com/", $referer)) {
  echo <<<EOL
<h1>Access Forbiden</h1>
<p>Please go away.</p>

EOL;
  exit();
}

$S = new Blp;

$extra = <<<EOF
<link rel="stylesheet"  href="css/tablesorter.css" type="text/css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="js/tablesorter/jquery.tablesorter.js"></script>
<script src="js/tablesorter/jquery.metadata.js"></script>

<script>
jQuery(document).ready(function($) {
  $("#blpmembers, #logagent, #memberpagecnt, #counter").tablesorter().addClass('tablesorter'); // attach class tablesorter to all except our counter
});
</script>
  
<style type="text/css">
div {
        padding: 10px 0;
}
</style>

EOF;

$h = array('title'=>"Web Statistics", 'extra'=>$extra,
           'banner'=>"<h1>Web Stats For <b>bartonphillips.com</b></h1>");
$b = array('msg1'=>"<p>Return to <a href='index.php'>Home Page</a></p>\n<hr/>");

list($top, $footer) = $S->getPageTopBottom($h, $b);

$page = file_get_contents("webstats.i.txt");

echo <<<EOF
$top
<p>This report is gethered once each hour. Results are limited to 20 records.</p>
$page
$footer
EOF;
?>
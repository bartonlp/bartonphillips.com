<?php
// Show the ip and counter tables
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp();

$self = $S->self;

$h->banner = "<h1>Country from URL sufix</h1>";
list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

if($code = $_GET['code']) {
  $n = $S->query("select description from urlcountrycodes where code='$code'");
  if($n) {
    $row = $S->fetchrow();
    $desc = $row[0];
    $other = "";
    if(preg_match("/(.*?)\s*(\(.*?\))$/", $desc, $m)) {
      $desc = $m[1];
      $other = "<p>$m[2]</p>";
    }
      
    echo <<<EOF
$top
<h2>Description for Code '$code':<br/>
$desc</h2>
$other

EOF;
  } else {
    echo <<<EOF
<h2>Code '$code' not found</h2>

EOF;
  }
} else {
  echo <<<EOF
$top
<form action="$self" method="get">
Enter the Country Code: <input type="text" name="code" /><br/>
<input type="submit" value="Submit"/>
</form>

EOF;
}
echo <<<EOF
$footer
EOF;

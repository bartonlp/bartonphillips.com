<?php
// Show the ip and counter tables
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

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

    // Use ipinfo.io to get the country for the ip
    $cmd = "http://ipinfo.io/$code";
    $ch = curl_init($cmd);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $loc = json_decode(curl_exec($ch));

    $locstr = <<<EOF
  <ul class="user-info">
    <li>You got here via: <span class='green'><i>{$_SERVER['SERVER_NAME']}</i>.</span>$ref</li>

    <li>User Agent String is:<br>
    <i class='green'>$S->agent</i></li>
    <li>IP Address: <i class='green'>$S->ip</i></li>
    <li>Hostname: <i class='green'>$loc->hostname</i></li>
    <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
    <li>GPS Loc: <i class='green'>$loc->loc</i></li>
    <li>ISP: <i class='green'>$loc->org</i></li>
  </ul>
  EOF;

    echo $locstr;
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

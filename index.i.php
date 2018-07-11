<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2018-04-25 -- change blp code to 8653 after a bot had old code
// BLP 2018-03-06 -- break up index.php into index.i.php, index.js and index.css

// Check if any of my sites have items that need to be added to the git repository

function dogit() {
  $ret = '';
  $any = false;
  
  foreach(['/vendor/bartonlp/site-class', '/applitec', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/bartonphillips.org', '/granbyrotary.org', '/messiah'] as $site) {
    chdir("/var/www/$site");
    exec("git status", $out); // put results into $out
    $out = implode("\n", $out);
    if(!preg_match('/working directory clean/s', $out)) {
      $any = true;
    }
  }
  return $any;
}

// if this is a bot don't bother with getting a location.

if($S->isBot) {
  $locstr = <<<EOF
<ul calss="user-info">
  <li>IP Address: <i class='green'>$S->ip</i></li>
</ul>
EOF;
} else {
  $ref = $_SERVER['HTTP_REFERER'];

  if($ref) {
    if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
    $ref =<<<EOF
<li>You came to this site from: <i class='green'>$ref</i></li>
EOF;
  }
  
  // Use ipinfo.io to get the country for the ip
  $cmd = "http://ipinfo.io/$S->ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));

  $clientname = gethostbyaddr($S->ip);
  $locstr = <<<EOF
<ul class="user-info">
  $ref
  <li>User Agent String is:<br>
    <i class='green'>$S->agent</i></li>
  <li>IP Address: <i class='green'>$S->ip</i></li>
  <li>Clientname: <i class='green'>$clientname</i></li>
<!--  <li>Hostname: <i class='green'>$loc->hostname</i></li> -->
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li>GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
</ul>
<span id="TrackerCount"></span>
EOF;
} // End of if(isBot..

// Do we have a cookie? If not offer to register
// BLP 2018-02-10 -- if we have a cookie and it is me then set $adminStuff

if(!($hereId = $_COOKIE['SiteId'])) {
  $S->query("select count, date(created) from $S->masterdb.logagent ".
            "where ip='$S->ip' and agent='$S->agent' and site='$S->siteName'");

  list($hereCount, $created) = $S->fetchrow('num');
  if($hereCount > 1) {
    $hereMsg =<<<EOF
<div class="hereMsg">You have been to our site $hereCount since $created<br>
Why not <a href="register.php">register</a>
</div>
EOF;
  }
} else {
  $sql = "select name from members where id=$hereId";
  
  if($n = $S->query($sql)) {
    list($memberName) = $S->fetchrow('num');
    if($memberName == "Barton Phillips") {
      // don't do this for a while
      //$GIT = dogit();

      // BLP 2018-02-10 -- If it is me do the 'adminStuff'
      $adminStuff = require("/var/www/bartonlp/adminsites.php");
    }
    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  } else {
    error_log("$S->siteName: members id ($hereId) not found at line ".__LINE__);
  }
}

$GIT = $GIT ? 1 : 0;

// BLP 2018-02-10 -- The above should have found $adminStuff if we have a cookie
// Do Admin Stuff if it is me

if($_GET['blp'] == "8653") { // BLP 2018-04-25 -- new code
  if(!$adminStuff) {
    $blp = $_GET['blp'];
    
    //error_log("bartonphillips.com/index.php: No 'adminStuff'");
    
    if($blp) {
      $blplogin = $blp;
      error_log("bartonphillips.com/index.i.php. Using blp: $S->ip, $S->agent");
    }
    $adminStuff = require("/var/www/bartonlp/adminsites.php");
  }
}

$ip = $S->ip;

// Get todays count and visitors from daycounts table


$S->query("select sum(`real`+bots) as count, sum(visits) as visits ".
          "from $S->masterdb.daycounts ".
          "where date=current_date() and site='$S->siteName'");

$row = $S->fetchrow('assoc');
$count = number_format($row['count'], 0, "", ",");
$visits = number_format($row['visits'], 0, "", ",");

// Get total number for today.
$n = $S->query("select distinct ip from $S->masterdb.tracker where lasttime>=current_date() and site='$S->siteName'");
$visitors = number_format($n, 0, "", ",");

$visitors .= ($visitors < 2) ? " visitor" : " visitors";

date_default_timezone_set("America/New_York");

$date = date("l F j, Y H:i:s T");

// use the Dom class to get the Sans '.diary h2' as text.
// This class is great for scrubing sites.

use PHPHtmlParser\Dom;

try {
  $dom = new Dom;
  $dom->load('https://isc.sans.edu/');
  $text = $dom->find(".diary h2 a")->text;
  //echo "text: $text<br>";
  $sans = "<span class='sans'>$text</span>";

  // Check on the infocon status

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://isc.sans.edu/api/infocon?json");
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $color = curl_exec($ch);
  curl_close($ch);
  $color = json_decode($color)->status;

  if(!empty($color) && $color != 'green') {
    switch($color) {
      case 'yellow':
        $style = 'style="color: yellow; background-color: black; padding: 0 .5rem;"';
        break;
      case 'red':
        $style = 'style="color: red; background-color: black; padding: 0 .5rem;"';
        break;
    }
    $status = "<h2>The Internet is under attack. "+
              "<a href='https://isc.sans.edu'>isc.sans.edu</a> "+
              "status is <span $style>$color</span></h2>";
  }

  $stormwatchpage =<<<EOF
  <section id='stormwatch'>
<hr>
<!-- # SANS Infocon Status https://isc.sans.edu/api/infocon -->
<div class="center">
<a target="_blank" href="https://isc.sans.edu"><img alt="Internet Storm Center Infocon Status"
src="https://isc.sans.edu/images/status_$color.gif">$sans</a>
</div>
</section>
EOF;

} catch(Exception $e) {
  $stormwatchpage =<<<EOF
<hr>
<center><h2>Error Contacting <i>https://isc.sans.edu</i></h2></center>
EOF;
}

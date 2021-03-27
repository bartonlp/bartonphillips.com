<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2021-03-24 -- move $_GET['blp'] to top so it is available for all requires of adminsites.php  
// BLP 2018-04-25 -- change blp code to 8653 after a bot had old code
// BLP 2018-03-06 -- break up index.php into index.i.php, index.js and index.css

// Check if any of my sites have items that need to be added to the git repository

$blp = $_GET['blp']; // Get the secret value if supplied.
  
function dogit() {
  $ret = '';
  $any1 = '';
  $any2 = '';
  
  foreach(['/vendor/bartonlp/site-class', '/applitec', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/allnaturalcleaningcompany'] as $site) {
    chdir("/var/www/$site");
    exec("git status", $out); // put results into $out
    $out = implode("\n", $out);
    if(preg_match('/nothing to commit, working tree clean/s', $out) === 0) {
      $any1 = ' *';
    } else error_log("need to commit");
    
    if(preg_match("~'origin/master' by (\d+) commit~s", $out, $m) === 1) {
      $any2 = ' !';
    }
  }
  return [$any1, $any2];
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
// The $hereId is the index into the members table. 

if(!($hereId = $_COOKIE['SiteId'])) { // if no cookie
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
} else { // we found a cookie and it is $hereId
  $sql = "select name, email from members where id=$hereId";
  
  if($n = $S->query($sql)) {
    list($memberName, $memberEmail) = $S->fetchrow('num');
    if($memberEmail == "bartonphillips@gmail.com") {
      $GIT = dogit(); // This is an array of two bools
      // BLP 2018-02-10 -- If it is me do the 'adminStuff'
      $adminStuff = require("/var/www/bartonlp/adminsites.php");
    }
    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  } else {
    //error_log("$S->siteName: members id ($hereId) not found at line ".__LINE__);
    header("Location: ../bartonlp/register.php");
    exit();
  }
}

// BLP 2018-02-10 -- The above should have found $adminStuff if we have a cookie
// BLP 2021-03-24 -- $blp is set at the very top so it is available here and above for the require of adminsites.php

if($blp == "8653" && !$adminStuff) { // BLP 2018-04-25 -- new code
  //error_log("bartonphillips.com/index.php: No 'adminStuff'");
  // if we didn't load adminsites above then who is this? I guess I will still let them see the
  // adminsite. I will review logs and decide.
  
  error_log("bartonphillips.com/index.i.php. Using blp: $S->ip, $S->agent");
  $adminStuff = require("/var/www/bartonlp/adminsites.php");
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


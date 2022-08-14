<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2022-04-18 - Removed all git related stuff as gitstatus.php stopped working.
// See commit ca68a04b268483d180ea8a160fc4e90c185c2050 under bartonphillipsnet.
/*
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `finger` varchar(50) NOT NULL,
  `count` int DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`,`finger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3

The myip table is in $S->masterdb (which should be 'bartonlp') database
  'myIp' will be all of the computers that I have used in the last THREE days.
  A cron job removes entries that are older than three days and not my HOME ip address.
  
CREATE TABLE `myip` (
  `myIp` varchar(40) NOT NULL DEFAULT '',
  `count` int DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`myIp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 
*/

$BLP = $_GET['blp']; // Get the secret value if supplied.
date_default_timezone_set("America/New_York");
$date = date("l F j, Y H:i:s T");

// if this is a bot don't bother with getting a location. And it will not have a SiteId.

if($S->isBot) {
  //echo "BOT<br>";
  $locstr = <<<EOF
<ul class="user-info">
  <li style="color: red">You Are a Robot</li>
  <li>IP Address: <i class='green'>$S->ip</i></li>
</ul>
EOF;
  return; // just return with what we have so far.
}

// Not a bot. Get ipinfo.io information
  
if($ref = $_SERVER['HTTP_REFERER']) {
  if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
  $ref =<<<EOF
<li>You came to this site from: <i class='green'>$ref</i></li>
EOF;
}
  
// Use ipinfo.io to get the country for the ip

$cmd = "http://ipinfo.io/$S->ip";
$loc = json_decode(file_get_contents($cmd));

$clientname = gethostbyaddr($S->ip);

$locstr = <<<EOF
<ul class="user-info">
  $ref
  <li>User Agent String is:<br>
    <i class='green'>$S->agent</i></li>
  <li>IP Address: <i class='green'>$S->ip</i></li>
  <li>Clientname: <i class='green'>$clientname</i></li>
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li id="location">GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
  <li id="geo">Your Location: <i class='green'></i></li>
  <li id="finger">Your fingerprint: <i class='green'></i></li>
</ul>
<span id="TrackerCount"></span>
EOF;

// Do we have a cookie? If not offer to register
// The 'SiteId' cookie is the finger and email address separated by a colen (:). 
// If we have a cookie and it is me then set $adminStuff
// The $cookieIp is the last ip address for this browser/CPU from $_COOKIE

if(!($fingerEmail = $_COOKIE['SiteId'])) { // NO COOKIE
  $count = 0;
  $n = $S->query("select count from $S->masterdb.logagent where ip='$S->ip'");
  if($n = 1) goto onlyOne; // kinda hate to use a goto but what the hell
  
  while($cnt = $S->fetchrow('num')[0]) {
    $count += $cnt;
  }

  $hereMsg =<<<EOF
<div class="hereMsg">You have been here $count time. Why not <a href="https://www.bartonphillips.com/register.php">Register</a></div>
EOF;
} else { // There is a cookie
  [$cookieFinger, $cookieEmail] = explode(':', $fingerEmail); // The cookie is 'IP:Email'

  $sql = "select name from members where finger='$cookieFinger' and email='$cookieEmail'";

  if($S->query($sql)) {
    // Found the record.

    [$memberName] = $S->fetchrow('num');
    
    $hereMsg = "<div class='hereMsg'>Welcome $memberName</div>";

    if($cookieEmail == "bartonphillips@gmail.com") {
      $adminStuff = require("adminsites.php");
    }
  } else { // Only been here one time so just show this message.
onlyOne:

    $hereMsg = <<<EOF
<div class="hereMsg">Why not <a href="https://www.bartonphillips.com/register.php">Register</a></div>
EOF;
  }
}

$fingers = file_get_contents("https://bartonphillips.net/myfingerprints.json");
//vardump("fingers", $fingers);

if($BLP == "8653" && !$adminStuff) {
  // if we didn't load adminsites above then who is this? I guess I will still let them see the
  // adminsite. I will review logs and decide.

  error_log("bartonphillips.com/index.i.php. The secret code was given as a query: $S->ip, $S->agent");
  $adminStuff = require("adminsites.php");
}

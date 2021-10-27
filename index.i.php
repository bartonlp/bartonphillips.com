<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2021-10-03 -- add set cookie and set members
// BLP 2021-09-27 -- NO TOO CONFUSING (add homeIp to members if it is me.)
// BLP 2021-09-23 -- remove several 'else' and just return.
// BLP 2021-09-23 -- table layout of members changed.
// BLP 2021-09-22 -- moved adminsites.php from bartonphillipsnet to bartonphillips.com.
// BLP 2021-09-21 -- Change the way we register and save myIp. See index.php, register.php
// BLP 2021-08-21 -- add tysonweb and newbernzig.com to dogit().
// BLP 2021-03-24 -- move $_GET['blp'] to top so it is available for all requires of adminsites.php  
// BLP 2018-04-25 -- change blp code to 8653 after a bot had old code
// BLP 2018-03-06 -- break up index.php into index.i.php, index.js and index.css
/*
  BLP 2021-09-23 -- Removed id and changed key to email only.
  The members table is in the bartonphillips database
  The 'ip' is the last 'ip' that was set for 'bartonphillips@gmail.com'
  
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3

  The myip table is in $S->masterdb (which should be 'bartonlp') database
  'myIp' will be all of the computers that I have used.
  
CREATE TABLE `myip` (
  `myIp` varchar(40) NOT NULL DEFAULT '',
  `createtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`myIp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 
*/

$blp = $_GET['blp']; // Get the secret value if supplied.
date_default_timezone_set("America/New_York");
$date = date("l F j, Y H:i:s T");

// Check if any of my sites have items that need to be added to the git repository
// dogit() is called below to set the $GIT array. $GIT is used by adminsites.php
// (bartonphillipsnet) to indicate 1) items to commit, 2) items that need to be pushed

function dogit() {
  array($any);
  
  foreach(['/vendor/bartonlp/site-class', '/applitec', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/allnaturalcleaningcompany', '/tysonweb', '/newbernzig.com'] as $site) {

    chdir("/var/www/$site");
    exec("git status", $out); // put results into $out
    $out = implode("\n", $out);

    // If the phrase below is found then there is nothing to commit.
    
    if(preg_match('/nothing to commit, working tree clean/s', $out) === 0) {
      // Needs to be commited
      
      $any[0] = ' *';
    }

    // If the phrase below is found then we need to 'push' to github.
    
    if(preg_match("~'origin/master' by (\d+) commit~s", $out, $m) === 1) {
      // We need a push
      
      $any[1] = ' !';
    }
  }
  return $any;
}

// if this is a bot don't bother with getting a location. And it will not have a SiteId.

if($S->isBot) {
  $locstr = <<<EOF
<ul calss="user-info">
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
  <li id="geo">Your Location: <i class='green'></i></li>
  <li id="finger">Your fingerprint: <i class='green'></i></li>
</ul>
<span id="TrackerCount"></span>
EOF;

// Do we have a cookie? If not offer to register
// BLP 2021-09-21 -- new logic for myip and members tables. See index.php and register.php.
// $ipEmail is now the ip and email address separated by a colen (:). 
// BLP 2018-02-10 -- if we have a cookie and it is me then set $adminStuff
// The $myIp is the last ip address for this browser/CPU from $_COOKIE

if(!($ipEmail = $_COOKIE['SiteId'])) { // if no cookie
  // check logagent table to see how may times this ip has been here.
  
  $S->query("select count, date(created) from $S->masterdb.logagent ".
            "where ip='$S->ip' and agent='$S->agent' and site='$S->siteName'");

  [$hereCount, $created] = $S->fetchrow('num');

  if($hereCount > 1) {
    $hereMsg =<<<EOF
<div class="hereMsg">You have been to our site $hereCount since $created<br>
Why not <a href="register.php">register</a>
</div>
EOF;
  }
  
  return; // return with what we have so far.
} 

// BLP 2021-09-21 -- break $ipEmail into $myIp and $cookieEmail

[$myIp, $cookieEmail] = explode(':', $ipEmail);

// BLP 2021-09-21 -- If this cookie email is ME then do special stuff

//vardump("cookie", $cookieEmail);

if($cookieEmail == "bartonphillips@gmail.com") {
  $GIT = dogit(); // This is an array, $GIT[0] could be an '*' while $GIT[1] could be an '!'. It is used by adminsites.php

  // BLP 2018-02-10 -- If it is me do the 'adminStuff'
  
  $adminStuff = require("adminsites.php");

  // BLP 2021-09-21 -- insert/update the ip with the current ip
    
  $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
         "on duplicate key update myIp='$S->ip', lasttime=now()";

  $S->query($sql);

  // BLP 2021-10-03 -- Get the ip from members

  $sql = "select ip, agent from members where email='bartonphillips@gmail.com'";
  $S->query($sql);
  [$memberIp, $memberAgent] = $S->fetchrow('num');

  // BLP 2021-10-03 -- if memberIp is not our current ip then update the members table.

  if($memberIp !== $S->ip || $memberAgent !== $S->agent) {
    $sql = "insert into members (name, email, ip, agent, created, lasttime) ". // The insert should fail with duplicate
           "values('Barton Phillips', 'bartonphillips@gmail.com', '$S->ip', '$S->agent', now(), now()) ".
           "on duplicate key update ip='$S->ip', agent='$S->agent', lasttime=now()"; // Update the ip, agent and lasttime

    $S->query($sql);
  }

  // BLP 2021-10-03 --  check my cookie IP against the current ip 
    
  if($myIp !== $S->ip) {
    // BLP 2021-10-03 -- Update my cookie with the current ip address

    if($S->setSiteCookie('SiteId', "$S->ip:$cookieEmail", date('U') + 31536000, '/') === false) {
      echo "Can't set cookie in register.php<br>";
      throw(new Exception("Can't set cookie register.php " . __LINE__));
    }
  }
}

// BLP 2021-09-21 -- We do this for everyone if we found a cookie. Note change to members table.
// See the header above, the key is 'email' and 'name' and 'id' are no longer keys.
  
$sql = "select name from members where email='$cookieEmail'";
  
if($S->query($sql)) {
  // Found the record.
    
  [$memberName] = $S->fetchrow('num');
   
  $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
} else {
  // BLP 2021-09-21 -- If the cookie email is not found goto register.php
    
  error_log("$S->siteName/index.i.php: members ipEmail: '$ipEmail' not found at line ".__LINE__);
  header("Location: register.php");
  exit();
}

// BLP 2018-02-10 -- The above should have found $adminStuff if we have a cookie
// BLP 2021-03-24 -- $blp is set at the very top so it is available here and above for the require of adminsites.php

if($blp == "8653" && !$adminStuff) { // BLP 2018-04-25 -- new code
  // if we didn't load adminsites above then who is this? I guess I will still let them see the
  // adminsite. I will review logs and decide.
  
  error_log("bartonphillips.com/index.i.php. Using blp but not ME: $S->ip, $S->agent");
  $adminStuff = require("adminsites.php");
}

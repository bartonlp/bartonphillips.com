<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2023-09-27 - add name to SiteId

$ip = $_SERVER['REMOTE_ADDR'];
if($ip == "192.241.1332.229") $ip .= ":SERVER";
$agent = $_SERVER['HTTP_USER_AGENT'];
if(empty($agent)) $agent = "NO_AGENT";
$self = htmlentities($_SERVER['PHP_SELF']);

if(!class_exists("Database")) {
  header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=Bartonphillips&page=$self, ip=$ip, agent=$agent");
}

/*
// BLP 2023-10-12 - Primary key is now (name,email,finger,ip)

CREATE TABLE `members` (
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `finger` varchar(50) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `count` int DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`name`,`email`,`finger`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

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

$cmd = "https://ipinfo.io/$S->ip";
$loc = json_decode(file_get_contents($cmd));

$bigdatakey = require '/var/www/PASSWORDS/BigDataCloudAPI-key';
//$ip = '45.148.10.172';
if(($json = file_get_contents("https://api-bdc.net/data/user-risk?ip=$ip&key=$bigdatakey")) === false) {
  error_log("index.i.php: ip=$ip, key=$bigdatakey, api-bdc.net/data/user-rick failed");
} else {
  $istor = json_decode($json);
  $istor = json_decode($json);
  $istor->id = $S->LAST_ID;
  $istor->ip = $ip;
  $istor->site = $S->siteName;
  $istor->page = $S->self;
  $istor->agent = $S->agent;
}

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
  <li>Your record: <i class='green'>$S->LAST_ID</i></li>
</ul>
<span id="TrackerCount"></span>
EOF;

// Do we have a cookie? If not offer to register
// The 'SiteId' cookie is the name, finger and email address separated by a colen (:). 
// If we have a cookie and it is me then set $adminStuff

// Do we have a SiteId cookie?

if(!($nameFingerEmail = $_COOKIE['SiteId'])) { // NO COOKIE
  $count = 0;

  // Has this ip ever visited our site?
  
  if($S->sql("select count from $S->masterdb.logagent where ip='$S->ip' and site='$S->siteName'")) {
    // Yes get the counts.
    
    while([$cnt] = $S->fetchrow('num')) {
      $count += $cnt;
    }

    $hereMsg =<<<EOF
<div class="hereMsg">You have been here $count time. Why not <a href="https://www.bartonphillips.com/register.php">Register</a></div>
EOF;
  } else {
    // This ip has never been here.

    $hereMsg = <<<EOF
<div class="hereMsg">Why not <a href="https://www.bartonphillips.com/register.php">Register</a></div>
EOF;
  }
} else { // There is a cookie
  [$cookieName, $cookieFinger, $cookieEmail] = explode(':', $nameFingerEmail); // The cookie is 'name:finger:email'

  if($S->sql("select ip from bartonphillips.members where finger='$cookieFinger' and email='$cookieEmail' and name='$cookieName'")) {
    // Found the records.

    while($ip = $S->fetchrow('num')[0]) {
      if($ip == $S->ip) {
        continue;
      }

      if(!$S->sql("select ip from bartonphillips.members where finger='$cookieFinger' and email='$cookieEmail' and name='$cookieName' and ip='$S->ip'")) {
        // This ip does not exists for this key.
        // So we should add a new record for this new ip.

        $S->sql("insert into bartonphillips.members (ip, name, email, finger, count, created, lasttime) ".
                  "values('$S->ip', '$cookieName', '$cookieEmail', '$cookieFinger', 1, now(), now())");

        $S->sql("insert into $S->masterdb.myip (myIp, count, createtime, lasttime) ".
                  "values('$S->ip', 1, now(), now()) ".
                  "on duplicate key update count=count+1, lasttime=now()");
        
        error_log("index.i.php: ip=$ip, new ip added to members tabl for $cookieName, $cookieEmail, $cookieFinger, insert/update myip count");
      }
    }

    $hereMsg = "<div class='hereMsg'>Welcome $cookieName</div>";

    if($cookieEmail == "bartonphillips@gmail.com") {
      $adminStuff = require("adminsites.php");
    }
  } else { // Can't find the record
    error_log("index.i.php: Not found in members table, $cookieFinger, $cookieEmail, $cookieName");
    //echo "Not found in members table<br>";
  }
}

// BLP 2023-07-24 - fingers no longer used.
//$fingers = file_get_contents("https://bartonphillips.net/myfingerprints.json");
//$fingers = require_once("/var/www/bartonphillipsnet/myfingerprints.php");  
//vardump("fingers", $fingers);

if($BLP == "8653" && !$adminStuff) {
  // if we didn't load adminsites above then who is this? I guess I will still let them see the
  // adminsite. I will review logs and decide.

  error_log("index.i.php: ip=$S->ip, agent=$S->agent. The secret code was given as a query");
  $adminStuff = require("adminsites.php");
}

<?php
// Main page for bartonphillips.com
// BLP 2017-03-23 -- set up to work with https

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

// Check if any of my sites have items that need to be added

function dogit() {
    $ret = '';

  $any = false;
  
  foreach(['/vendor/bartonlp/site-class', '/applitec', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/bartonphillips.org', '/granbyrotary.org', '/messiah'] as $site) {
    chdir("/var/www/$site");
    exec("git status", $out);
    $out = implode("\n", $out);
    if(!preg_match('/working directory clean/s', $out)) {
      $any = true;
    }
  }
  return $any;
}

// if this is a bot don't bother with getting a location.

if($S->isBot) {
  $locstr = '';
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
EOF;
} // End of if(isBot..

// Do we have a cookie? If not offer to register

if(!($hereId = $_COOKIE['SiteId'])) {
  $S->query("select count, date(created) from $S->masterdb.logagent ".
            "where ip='$S->ip' and agent='$S->agent' and site='$S->siteName'");

  list($hereCount, $created) = $S->fetchrow('num');
  if($hereCount > 1) {
    $hereMsg =<<<EOF
<div class="hereMsg">You have been to our site $hereCount since $created<br>
Why not <a target="_blank" href="register.php">register</a>
</div>
EOF;
  }
} else {
  $sql = "select name from members where id=$hereId";
  
  if($n = $S->query($sql)) {
    list($memberName) = $S->fetchrow('num');
    if($memberName == "Barton Phillips") {
      $GIT = dogit();
    }
    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  } else {
    error_log("$S->siteName: members id ($hereId) not found at line ".__LINE__);
  }
}

// css/blp.css is included in head.i.php

$h->css = <<<EOF
  <!-- Local CSS -->
  <style>
.locstr {
  margin: 1rem 0;
}
.user-info {
  line-height: 1rem;
  margin: 0px;
}
.hereMsg {
  font-size: 1.2rem;
  font-weight: bold;
  padding-top: 1rem;
}
.mylinks {
  text-align: center;
  margin: auto;
}
/* My Sites */
.mysites {
  border-spacing: .5rem;
}
.mysites th {
  background-color: #FCF6CF;
  padding: .2rem;
  border-radius: .2rem;
  border: 1px solid black;
}
.mysites button {
  width: 100%;
  font-weight: bold;
  font-size: 1rem;
  display: table-cell;
  background-color: #FCF6CF;
  border: none;
  cursor: pointer;
}
.mysites button:hover {
  color: green;
}
.mysites a {
  text-decoration: none;
}
/* Images */
#blpimg { /* My Logo Image */
  float: left;
  padding: 5px 10px;
}
#octocat { /* GitHub Image */
  width: 80px;
  vertical-align: bottom;
}
/* Colors */
.green {
  color: green;
}
.red {
  color: red;
}
/* Sans */
.sans {
  vertical-align: 40px;
}
/* Sections */
#browser-info { /* section */
  border-top: 1px solid gray;
}
#blog { /* section */
  width: 40%;
  text-align: center;
  background-color: #FCF6CF;
  padding: 20px;
  margin: auto;
  border: 1px solid #696969;
}

#mysites { /* section */
}
#projects {
  font-size: 1.5rem;
}
#flex-section {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  border: 1px solid black;
  background-color: #FEFFF1;
}

#interesting, #adminstuff, #internet {
  padding: .5rem;
  border-left: 1px solid black;
}
#github { /* section */
  padding: .5rem;
}
@media (max-width: 1000px) {
  #flex-section {
    display: block;
    border: none;
  }
  #github, #interesting, #adminstuff, #internet {
    border: none;
  }
}
@media (max-width: 400px) {
  img[src="https://isc.sans.edu/images/status.gif"] {
    width: 300px;
  }
}
  </style>
EOF;

$h->script = <<<EOF
<script type="application/ld+json">
{
  "@context": "http://schema.org/",
  "@type": "Person",
  "name": "Barton Phillips",
  "url": "http://www.bartonphillips.com",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Newbury Park",
    "addressRegion": "CA",
    "postalCode": "91320",
    "streetAddress": "828 Cayo Grande Ct."
  },
  "email": "mailto:bartonphillips@gmail.com",
  "image": "http://www.bartonphillips.net/images/blp-image.png",
  "jobTitle": "Retired",
  "telephone": "(805) 716-3614",
  "birthDate": "1944-04-11",
  "birthPlace": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "New York City",
      "addressRegion": "NY",
      "addressCountry": "US"
    }
  }
}
</script>
EOF;

$h->script .= <<<EOF
  <!-- local script -->
  <script src="https://bartonphillips.net/js/phpdate.js"></script>
  <script>
jQuery(document).ready(function($) {
  var weewx = '';

  // Date Today
  setInterval(function() {
    var d = date("l F j, Y");
    var t = date("H:i:s T"); // from phpdate.js
    $("#datetoday").html("<span class='green'>"+d+"</span><br>Your Time is: <span class='green'>"+t+"</span>");
  }, 1000);

  // We set this in PHP above and now if it is true we will notify me.

  if($GIT == true) {
    function notifyMe(msg) {
      // Let's check if the browser supports notifications
      if (!("Notification" in window)) {
        alert("This browser does not support desktop notification");
      } else if(Notification.permission === "granted") {
        //console.log("hi 0");

        var notification = new Notification("Hi there!", {
              body: msg,
              icon: "https://bartonphillips.net/images/favicon.ico"
        });
      } else if(Notification.permission !== "denied") {
        //console.log("hi ask permision 2");

        Notification.requestPermission(function (permission) {
          // If the user accepts, let's create a notification
          //console.log("hi 3");
          if(permission === "granted") {
            var notification = new Notification("First Time!", {
              body: msg,
              icon: "https://bartonphillips.net/images/favicon.ico"
            });
          }
        });
      }

      notification.onclick = function(event) {
        event.preventDefault(); // prevent the browser from focusing the Notification's tab
        window.open('https://www.bartonphillips.com/gitstatus.php', '_blank');
        notification.close();
      }
    }

    notifyMe("Your files are not up to date");
  }
});
  </script>  
EOF;

$h->title = $S->siteName;

$h->banner = <<<EOF
<div class='center'>
<h1>$S->mainTitle</h1>
<h2>
<a target='_blank' href='https://www.bartonlp.com/toweewx.php'>My Home Weather Station</a>
</h2>
<h3><a target="_blank" href="aboutweewx.php">About My Weather Station</a></h3>
</div>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

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

// Do Admin Stuff if it is me

if($S->isMe() || ($_GET['blp'] == "7098")) {
  if($_GET['blp']) {
    //echo $_GET['blp'] . "<br>";
    $blplogin = $_GET['blp'];
    error_log("Bartonphillips index.php. Using blp: $S->ip, $S->agent");
  }
  $adminStuff = require("/var/www/bartonlp/adminsites.php");
}

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
  //vardump("color", $color);
  $color = json_decode($color)->status;
  //echo "color: $color<br>";

  if(!empty($color) && $color != 'green') {
    switch($color) {
      case 'yellow':
        $style = 'style="color: yellow; background-color: black; padding: 0 .5rem;"';
        break;
      case 'red':
        $style = 'style="color: red; background-color: black; padding: 0 .5rem;"';
        break;
    }
    $status = "<h2>The Internet is under attack. <a href='https://isc.sans.edu'>isc.sans.edu</a> status is <span $style>$color</span></h2>";
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

// ***************
// Render the page
// ***************

echo <<<EOF
$top
$status
<section id='browser-info'>
$hereMsg
<div class="locstr">
   Our domain is <i>bartonphillips.com</i><br/>
   $locstr
Start: <span class='green'>$date in New Bern, NC</span><br>
Today is: <span id="datetoday">$date</span></div>
<hr>
<p>
   This page is dynamically generated using PHP on our server at
   <a target="_blank" href="https://www.digitalocean.com/">DigitalOcean.com</a>.
   Very little JavaScript is used in this page. We only collect &quot;Google Analitics&quot; COOKIES and
   a COOKIE called 'mytime' which is used to tell how long some anonymous someone has stayed on our site.
   We don't track you.
   We do collect anonymous information for page counting and analysis only. <a target="_blank" href="privacy.php">Our privacy statement</a>.</p>
<p>
   <span class='red'>However</span>, some of the pages we link to do collect tracking information
   and COOKIES and make extensive use of JavaScript.
</p>
</section>

<section id="blog">
<a target="_blank" href="proxy.php?http://myblog.bartonphillips.com">My BLOG with tips and tricks</a>.
</section>

<section id="mysites">
<h2 class="center">Visit one of the other web sites designed by Barton Phillips</h2>
<table class="mysites mylinks">
<tbody>
<tr>
<th><a target="_blank" href="https://www.granbyrotary.org"><button>The Granby Rotary Club</button></a></th>
<th><a target="_blank" href="https://www.applitec.com"><button>Applied Technology Resouces Inc.</button></a></th>
<th><a target="_blank" href="https://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a></th>
</tr>
<tr>
<th><a target="_blank" href="https://www.mountainmessiah.com"><button>Mountain Messiah</button></a></th>
<th><a target="_blank" href="https://www.bartonphillips.org"><button>bartonphillips.org</button></a></th>
<th><a target="_blank" href="https://www.bartonlp.com/toweewx.php"><button>My Weather Station</button></a></th>
</tr>
<tr>
<th><a target="_blank" href="https://www.bartonlp.com"><button>bartonlp.com</button></a></th>
<th><a target="_blank" href="http://www.bartonlp.org"><button>bartonlp.org</button></a></th>
<th><a target="_blank" href="http://www.bartonlp.org:7000/"><button>My node.js Page</button></a></th>
</tr>
</tbody>
</table>
</section>

<div id="flex-section">

<section id="github">
<h2>GitHub Projects</h2>
<ul>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/bartonphillips.com">My GitHub sites</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/site-class/">SiteClass</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/updatesite/">UpdateSite Class</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/rssfeed/">RssFeed Class</a></li>
</ul>
</section>

<section id="interesting">
<h2>Interesting Sites</h2>
<ul>
<li><a target="_blank" href="https://www.wunderground.com/personal-weather-station/dashboard?ID=KNCNEWBE48#history">
Weather Underground</a></li>
<li><a target="_blank" href="http://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="http://www.bartonphillips.com/spacestation.php">Space Station Location</a></li>
<li><a target="_blank" href="proxy.php?http://www.swam.us">Southwest Aquatic Master</a></li>
<li><a target="_blank" href="http://www.computerscienceonline.org">Computer Science Online</a></li>
<li><a target="_blank" href="https://developers.google.com/web/">Google/Web</a></li>
</ul>
</section>
$adminStuff
<section id="internet">
<h2>About the Internet</h2>
<ul>
<li><a target="_blank" href="historyofinternet.php">History &amp; Timeline</a></li>
<li><a target="_blank" href="howtheinternetworks.php">How It Works</a></li>
<li><a target="_blank" href="howtowritehtml.php">How To Write HTML</a></li>
<li><a target="_blank" href="buildawebsite.php">Build a Website</a></li>
</ul>
</section>
</div>

<section id="tips">
<h2>Helpful Programs and Tips</h2>
<ul>
<li><a target="_blank" href="https://www.bartonlp.com/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/base64.php">Decode Base 64</a></li>
<li><a target="_blank" href="https://www.bartonlp.org/pug-examples.php">Examples Using Pug</a>
<li><a target="_blank" href="javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a target="_blank" href="promise.php">Use AJAX and Promise</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/examples.js/user-test/worker.main.php">Demo using a Worker</a></li>
<li><a target="_blank" href="linuxmint-from-iso.php">How to Install Linux Mint via ISO from Disk</a></li>
<li><a target="_blank" href="testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a target="_blank" href="dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a target="_blank" href="localstorage.html">Local Storage Example: How To Resize An Image With JavaScript</a><br>
<li><a target="_blank" href="filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="easter-example.php">When is Easter and other holidays realted to Easter?</a><br>
<li><a target="_blank" href="urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="https://www.bartonlp.com/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/getIP.php">Check Ip Address</a></li>
<li><a target="_blank" href="https://wiki.amahi.org/index.php/Gmail_As_Relay_On_Ubuntu">
How to setup Linux Mint email via Gmail.com</a></li>
</ul>
</section>
<section id='projects'>
<a target="_blank" href='projects.php'>GitHub and PHPClasses projects</a>
</section>
$stormwatchpage
<hr>
$footer
EOF;

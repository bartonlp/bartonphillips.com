<?php
// Main page for bartonphillips.com
$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");
$S = new $_site['className']($_site); // takes an array if you want to change defaults

// if this is a bot don't bother with getting a location.

if($S->isBot) {
  $locstr = '';
} else {
  $cmd = "http://ipinfo.io/$S->ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));
  $locstr = "Hostname: $loc->hostname<br>$loc->city, $loc->region $loc->postal<br>Location: $loc->loc<br>ISP: $loc->org<br>";
  $ar = explode(",", $loc->loc);

  $locstr .= <<<EOF
<!--
<style>
#map {
  height: 10rem;
  width: 20rem;
  border: 2px solid black;
}
</style>

<div id="map"></div>
<br>
<script>
var myLocation = {lat: '$ar[0]', lng: '$ar[1]'};
//var point = '$loc->city';
var map;
function initMap() {
  var lat = parseFloat(myLocation.lat), lng = parseFloat(myLocation.lng);

  map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: lat, lng: lng},
          zoom: 10
        });
/*
  marker = new google.maps.Marker({
             position: {lat: lat, lng: lng},
             map: map,
             title: point
           });
*/
}
// This is my google maps key:   AIzaSyAKNGzjJmHY5BNrKET45uGxU7qGe08XQPI 
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKNGzjJmHY5BNrKET45uGxU7qGe08XQPI&callback=initMap"
async defer></script>
-->
EOF;
} // End of if(isBot..

// css/blp.css is included in head.i.php

$h->css = <<<EOF
  <!-- Local CSS -->
  <style>
#browser-info {
  border-top: 1px solid gray;
}
#blog {
  width: 40%;
  text-align: center;
  background-color: #FCF6CF;
  padding: 20px;
  margin: auto;
  border: 1px solid #696969;
}
#daycount {
  text-align: center;
  width: 90%;
  margin: auto;
  border: 1px solid black;
  background-color: #ECD087;
  padding-bottom: 20px;
}
#daycount ul {
  width: 80%;
  text-align: left;
  margin: auto;
}
ul {
  line-height: 200%;
}
#blpimg {
  float: left;
  padding: 5px 10px;
}
#octocat {
  width: 80px;
  vertical-align: bottom;
}
@media (max-width: 400px) {
  img[src="http://isc.sans.edu/images/status.gif"] {
    width: 300px;
  }
}
  </style>
EOF;

$h->script = <<<EOF
  <!-- local script -->
  <script src="js/phpdate.js"></script>
  <script>
jQuery(document).ready(function($) {
  var weewx = '';

  // Date Today
  setInterval(function() {
    var d = date("l F j, Y");
    var t = date("H:i:s"); // from phpdate.js
    $("#datetoday").html(d+"<br>The Time is: "+t);
  }, 1000);

  // BLP 2014-08-18 -- Kill caching on toweewx

  $("a[href='/blp/toweewx.php']").click(function() {
    $(this).attr("href", "/blp/toweewx.php");
  });

  $("a[href='webstats-new.php']").click(function() {
    $(this).attr("href", "webstats-new.php");
  });

  $("#weewx").click(function() {
    var we = $(this).attr('href');
    we = we.replace(/xx/, weewx);
    we = we.replace(/\?.*/,'');
    $(this).attr('href', we);
    return true;
  });

  $("#apcups").click(function() {
    var we = $(this).attr('href');
    we = we.replace(/xx/, weewx);
    we = we.replace(/\?.*/,'');
    $(this).attr('href', we);
    return true;
  });
});
  </script>  
EOF;

$h->title = $S->siteName;

$h->banner = <<<EOF
<div class='center'>
<h1>$S->mainTitle</h1>
<h2>
<a target='_blank' href='/blp/toweewx.php'>My Home Weather Station</a>
</h2>
<h3><a target="_blank" href="aboutweewx.php">About My Weather Station</a></h3>
</div>
EOF;

$ref = $_SERVER['HTTP_REFERER'];

if($ref) {
  if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
  $ref =<<<EOF
You came to this site from <i>$ref</i>.<br>
EOF;
}

list($top, $footer) = $S->getPageTopBottom($h, array('msg1'=>"<hr>"));

$ip = $S->ip;
$blpIp = $S->blpIp;

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
$date = date("l F j, Y");

// Render the page

echo <<<EOF
$top
<section id='browser-info'>
<p>
   Our domains are <i>bartonphillips.net</i>, <i>bartonphillips.org</i> and <i>bartonphillips.com</i><br/>
   You got here via <i>{$_SERVER['SERVER_NAME']}</i>.<br/>$ref
   Your browser's User Agent String: <i>$S->agent</i><br/>
   Your IP Address: <i>$S->ip</i><br/>$locstr
   Today is: <span id="datetoday">$date</span>
</p>
<hr>
<p>
   This page is dynamically generated using PHP on our server at
   <a target="_blank" href="http://www.digitalocean.com/">DigitalOcean.com</a>.
   Almost no JavaScript is used in this page. We collect no COOKIES. We don't track you.
   We do collect anonymous information for page counting etc. However, some of the
   pages we link to do collect tracking information and COOKIES and make extensive use
   of JavaScript.
</p> 
</section>

<section id="blog">
<a target="_blank" href="http://myblog.bartonphillips.com">My BLOG with tips and tricks</a>.
</section>

<section id="links">
<h2>Visit one of the other web sites designed by Barton Phillips</h2>
<ul>
<li><a target="_blank" href="http://www.granbyrotary.org">The Granby Rotary Club</a></li>
<li><a target="_blank" href="http://www.applitec.com">Applied Technology Resouces Inc.</a></li>
<li><a target="_blank" href="http://www.conejoskiclub.org/">Conajo Ski and Sports Club</a></li>
<li><a target="_blank" href="http://www.swam.us">South West Aquatic Masters</a></li>
<li><a target="_blank" href="http://bartonlp.com/html/toweewx.php">My Home Weather Station</a><br>
<li><a target="_blank" href="http://www.bartonlp.com">bartonlp.com, Expermental Site</a></li>
<li><a target="_blank" href="http://gitHub.bartonphillips.com">Barton Phillips GitHub site</a></li>
<li><a target="_blank" href="webstats.php">Web Stats</a></li>
<li><a target="_blank" href="http://bartonphillips.dyndns.org/apc.php">UPS</a></li>
</ul>

<h2>Interesting Sites</h2>
<ul>
<li><a target="_blank" href="http://www.wunderground.com/personal-weather-station/dashboard?ID=KCATHOUS54#history">
Weather Underground Near Me</a></li>
<li><a target="_blank" href="https://dashboard.opendns.com/">OpenDNS</a></li>
<li><a target="_blank" href="http://www.html5rocks.com/en/">HTML5 Rocks</a></li>
<li><a target="_blank" href="http://www.sitepoint.com">Site Point</a></li>
<li><a target="_blank" href="http://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="spacestation.php">ISS Overhead</a></li>
</ul>

<h2>About the Internet</h2>
<ul>
<li><a target="_blank" href="historyofinternet.php">The History and Timeline of the Internet</a></li>
<li><a target="_blank" href="howtheinternetworks.php">How the Internet Works</a></li>
<li><a target="_blank" href="howtowritehtml.php">Tutorial: How To Write HTML</a></li>
<li><a target="_blank" href="buildawebsite.php">So You Want to Build a Website</a></li>
</ul>

<h2>Helpful Programs and Tips</h2>
<ul>
<li><a target="_blank" href="linuxmint-from-iso.php">How to Install Linux Mint via ISO from Disk</a></li>
<li><a target="_blank" href="testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a target="_blank" href="dynamicscript.php">Dynamically create script tags using PHP or JavaScript</a></li>
<li><a target="_blank" href="localstorage.html">Local Storage Example: How To Resize An Image With JavaScript</a><br>
<li><a target="_blank" href="filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="mx330.php">How <b>NOT TO</b> Setup The Canon MX330 All-In-One Print/Scan/Copy/Fax
For Linux. <b>Instead buy an HP</b>.</a></li>
<li><a target="_blank" href="usinghosts.php">Why can't I access my home-hosted website from my own computer</a>? This is a common problem.</li>
<li><a target="_blank" href="easter-example.php">When is Easter and other holidays realted to Easter?</a><br>
<li><a target="_blank" href="http://www.phys.uu.nl/~vgent/easter/eastercalculator.htm">Site with lots of Easter and Passover Information</a><br> 
<li><a target="_blank" href="urlcountrycodes.php">Find the country give a url country code</a><br>
<li><a target="_blank" href="http://www.bartonlp.com/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="http://bartonlp.com/html/getIP.php">Check Ip Address</a></li>
<li><a target="_blank" href="https://wiki.amahi.org/index.php/Gmail_As_Relay_On_Ubuntu">
How to setup Linux Mint email via Gmail.com</a></li>
</ul>
<hr/>

<h2>PHP SiteClass Mini Framework</h2>
<p>This is a mini framework I have been using for almost 10 years. It has a database wrapper
and a number of methods that make my life a lot easier.</p>
<p>For example every web page needs a
&lt;head&gt; section and usually a &lt;footer&gt; as well as a &lt;header&gt;
(navigation and banner).
The framework makes these
things easy to live with.</p>
<p>The database wrapper lets you use several popular database engines
like 'mysql', 'mysqli', 'sqlite' and 'pod'. It is easy to use my framework with templeting
engines like Twig.</p>
<p>This framework is not &quot;All Things to All People&quot; like a number
of the well know frameworks try to be. This is a simple tool and therefore not nearly as
complex as some of the popular frameworks out there.</p>
<p>If you just have three or four virtual hosted sites and you need a quick way to get
everything working this is pretty easy.</p>
<p>The framework is hosted at<br><a target="_blank"
href="https://github.com/bartonlp/SiteClass">GitHub
<img id="octocat" src="/blp/images/Octocat.jpg"></a>
and also at
<a target="_blank"
href="http://www.phpclasses.org/package/9105-PHP-Create-database-driven-Web-sites.html">
<img src="/blp/images/phpclasses-logo.gif" width='180' height='59'></a>.
<br>Give it a try and let me know if you like it.</p>
<hr>

<h2>PHP Slide Show Class</h2>

<p>This class can be used to present a slide show of images.
It can extract lists of image files available on the local server or a remote Web server.</p>

<p>The image list is served to the browser which retrieves it with Javascript code that
performs AJAX requests to obtain the images to display.</p>

<p>For local server images the class returns a list of image file names.
For remote Web server, it retrieves a given remote page and parses it to return the
list of GIF, JPEG and PNG images linked from that page.</p>

<p>The Javascript libraries provided within this package control the
slide show presentation.</p>
  
<p>You can find my <b>Slide Show Class</b> at<br>
<a target="_blank"
href="http://github.com/bartonlp/slideshow">GitHub
<img id="octocat" src="/blp/images/Octocat.jpg"></a> and also at
<a target="_blank"
href="http://www.phpclasses.org/browse/author/592640.html">
<img src="/blp/images/phpclasses-logo.gif" width='180' height='59'
alt="php classes logo" /></a></p>
<hr/>
<h2>PHP MySql Slide Show Class</h2>

<p>This package can be used to present a slide show from images listed in a database.
The main class can retrieve lists of images to be displayed from a MySQL database table.</p>

<p>The class can also add or update the slideshow image lists in the database table,
The actual images can be stored on the filesystem or in the MySql table as base64 data.</p>
  
<p>You can find my <b>MySql Slide Show Class</b> at<br>
<a target="_blank" href="http://github.com/bartonlp/mysqlslideshow">GitHub
<img id="octocat" src="/blp/images/Octocat.jpg"></a> and also at
<a target="_blank" href="http://www.phpclasses.org/browse/author/592640.html">
<img src="/blp/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>

<!-- # SANS Infocon Status -->
<div>
<p>
<a target="_blank" href="https://isc.sans.org">
<img width="354" height="92" alt="Internet Storm Center Infocon Status"
src="http://bartonlp.com/html/images/internetstorm-icon.gif" />
</a>
</p>
</section>

<section id="daycount">
<p>There have been $count hits and $visits visits by $visitors today $date</p>
<ul>
<li>Hits are each time this page is accessed. If you do three refreshes in a row you have 3 hits.</li>
<li>Visits are hits that happen 10 minutes appart. Three refresses in a row will not change the number of hits, but if you wait
10 minutes between refresses (or other accesses) to our site that is a visit.</li>
<li>Visitors are seperate accesses by different IP Addresses.</li>
</ul>
</section>
$footer
EOF;

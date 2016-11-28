<?php
// Main page for bartonphillips.com
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

// if this is a bot don't bother with getting a location.

if($S->isBot) {
  $locstr = '';
} else {
  $ref = $_SERVER['HTTP_REFERER'];

  if($ref) {
    if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
    $ref =<<<EOF
<br>You came to this site from: <i class='green'>$ref</i>
EOF;
  }
  
  // Use ipinfo.io to get the country for the ip
  $cmd = "http://ipinfo.io/$S->ip";
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
} // End of if(isBot..

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
.green {
  color: green;
}
.red {
  color: red;
}
@media (max-width: 400px) {
  img[src="http://isc.sans.edu/images/status.gif"] {
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
  <script src="js/phpdate.js"></script>
  <script>
jQuery(document).ready(function($) {
  var weewx = '';

  // Date Today
  setInterval(function() {
    var d = date("l F j, Y");
    var t = date("H:i:s"); // from phpdate.js
    $("#datetoday").html("<span class='green'>"+d+"</span><br>The Time is: <span class='green'>"+t+"</span>");
  }, 1000);
});
  </script>  
EOF;

$h->title = $S->siteName;

$h->banner = <<<EOF
<div class='center'>
<h1>$S->mainTitle</h1>
<h2>
<a target='_blank' href='http://www.bartonlp.com/toweewx.php'>My Home Weather Station</a>
</h2>
<h3><a target="_blank" href="aboutweewx.php">About My Weather Station</a></h3>
</div>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, array('msg1'=>"<hr>"));

// Do we have a cookie? If not offer to register

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
    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  } else {
    error_log("$S->siteName: members id ($hereId) not found at line ".__LINE__);
  }
}

$ip = $S->ip;
$blpIp = $S->myIp;

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

if($S->isMe() || ($_GET['blp'] == "7098")) {
  if($_GET['blp']) {
    error_log("Bartonphillips index.php. Using blp: $S->ip, $S->agent");
  }
  $adminStuff = <<<EOF
<h2>Administration Links</h2>
<ul>
<li><a target="_blank" href="webstats.php">Web Stats</a></li>
</ul>
EOF;
}

// Render the page

echo <<<EOF
$top
<section id='browser-info'>
$hereMsg
<div class="locstr">
   Our domains are <i>bartonphillips.org</i> and <i>bartonphillips.com</i><br/>
   $locstr
Today is: <span id="datetoday">$date</span></div>
<hr>
<p>
   This page is dynamically generated using PHP on our server at
   <a target="_blank" href="http://www.digitalocean.com/">DigitalOcean.com</a>.
   Very little JavaScript is used in this page. We only collect &quot;Google Analitics&quot; COOKIES and
   a COOKIE called 'mytime' which is used to tell how long some anonymous someone has stayed on our site.
   We don't track you.
   We do collect anonymous information for page counting and analysis only. <a href="privacy.php">Our privacy statement</a>.</p>
<p>
   <span class='red'>However</span>, some of the pages we link to do collect tracking information
   and COOKIES and make extensive use of JavaScript.
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
<li><a target="_blank" href="http://www.allnaturalcleaningcompany.com">All Natural Cleaning</a></li>
<li><a target="_blank" href="http://www.mountainmessiah.com">Mountain Messiah</a></li>
<li><a target="_blank" href="http://www.swam.us">Southwest Aquatic Master</a></li>
<li><a target="_blank" href="http://www.bartonlp.com/toweewx.php">My Home Weather Station</a><br>
<li><a target="_blank" href="http://www.bartonlp.com">bartonlp.com, Expermental Site 1</a></li>
<li><a target="_blank" href="http://www.bartonlp.org">bartonlp.org, Expermental Site 2</a></li>
<li><a target="_blank" href="http://gitHub.bartonphillips.com">Barton Phillips GitHub site</a></li>
<li><a target="_blank" href="http://bartonlp.github.io/site-class/">SiteClass on GitHub</a></li>
<li><a target="_blank" href="http://bartonlp.github.io/updatesite/">UpdateSite Class on GitHub</a></li>
<li><a target="_blank" href="http://bartonphillips.dyndns.org/apc.php">UPS</a></li>
<li><a target="_blank" href="http://www.bartonlp.org:8080/">My node.js Page</a></li>
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
<li><a target="_blank" href="javascript-only.php">Java Script Only</a></li>
</ul>
$adminStuff
<h2>About the Internet</h2>
<ul>
<li><a target="_blank" href="historyofinternet.php">The History and Timeline of the Internet</a></li>
<li><a target="_blank" href="howtheinternetworks.php">How the Internet Works</a></li>
<li><a target="_blank" href="howtowritehtml.php">Tutorial: How To Write HTML</a></li>
<li><a target="_blank" href="buildawebsite.php">So You Want to Build a Website</a></li>
</ul>

<h2>Helpful Programs and Tips</h2>
<ul>
<li><a target="_blank" href="http://www.bartonlp.org/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a target="_blank" href="http://www.bartonlp.org/pug-examples.php">Examples Using Pug</a>
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
<li><a target="_blank" href="http://www.bartonlp.com/getIP.php">Check Ip Address</a></li>
<li><a target="_blank" href="https://wiki.amahi.org/index.php/Gmail_As_Relay_On_Ubuntu">
How to setup Linux Mint email via Gmail.com</a></li>
</ul>

<hr>
<h1>Review of New Service</h1>
<div itemscope itemtype="http://schema.org/Review">
<h4>
<a href="http://www.allnaturalcleaningcompany.com">
<span itemprop="itemReviewed">All Natural Cleaning Company</span></a>
<meta itemprop="url" content="http://www.allnaturalcleaningcompany.com">
</h4>
<p itemprop="reviewBody">This company is new to Albuquerque, NM, but its ideas are old fashion.
Clean with natural products. Don't poinsion oneself, ones family or ones employees.
Over all a great company with which to do business.</p>
<meta itemprop="author" content="Barton Phillips">
<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
<meta itemprop="ratingValue" content="5">
<meta itemprop="bestRating" content="5">
</div>
</div>
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
<p>The framework is hosted at<br>
<a target="_blank" href="https://github.com/bartonlp/SiteClass">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a>
and also at
<a target="_blank"
href="http://www.phpclasses.org/package/9105-PHP-Create-database-driven-Web-sites.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>.
<br>Give it a try and let me know if you like it.</p>
<hr>

<h2>UpdateSite Class</h2>
<p>This class works with SiteClass. It lets you create sections or articles in a webpage that can be edited via the
web browser. The sections are stored in a database (MySql is prefered).</p>
<p>Check out the repository at<br>
<a target="_blank" href="https://github.com/bartonlp/updatesite">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a>
and also at 
<a target="_blank"
href="http://www.phpclasses.org/package/10042-PHP-Updateable-section-in-a-website-.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>
and the <a target="_blank" href="https://bartonlp.github.io/updatesite">Documentation</a>.</p>
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
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank"
href="http://www.phpclasses.org/browse/author/592640.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
alt="php classes logo" /></a></p>
<hr/>
<h2>PHP MySql Slide Show Class</h2>

<p>This package can be used to present a slide show from images listed in a database.
The main class can retrieve lists of images to be displayed from a MySQL database table.</p>

<p>The class can also add or update the slideshow image lists in the database table,
The actual images can be stored on the filesystem or in the MySql table as base64 data.</p>
  
<p>You can find my <b>MySql Slide Show Class</b> at<br>
<a target="_blank" href="http://github.com/bartonlp/mysqlslideshow">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank" href="http://www.phpclasses.org/browse/author/592640.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>

<!-- # SANS Infocon Status -->
<div>
<p>
<a target="_blank" href="https://isc.sans.org">
<img width="354" height="92" alt="Internet Storm Center Infocon Status"
src="http://bartonphillips.net/images/internetstorm-icon.gif" />
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

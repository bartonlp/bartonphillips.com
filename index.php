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
<li>You came to this site from: <i class='green'>$ref</i></li>
EOF;
  }
  
  // Use ipinfo.io to get the country for the ip
  $cmd = "http://ipinfo.io/$S->ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));
  
  $locstr = <<<EOF
<ul class="user-info">
  $ref
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
@media (max-width: 600px) {
  table, tbody, tr, th, td {
    display: block;
  }
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
  <script src="http://bartonphillips.net/js/phpdate.js"></script>
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

list($top, $footer) = $S->getPageTopBottom($h);

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
    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  } else {
    error_log("$S->siteName: members id ($hereId) not found at line ".__LINE__);
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
$date = date("l F j, Y H:i:s T");

// Do Admin Stuff if it is me

if($S->isMe() || ($_GET['blp'] == "7098")) {
  if($_GET['blp']) {
    error_log("Bartonphillips index.php. Using blp: $S->ip, $S->agent");
  }
  $adminStuff = <<<EOF
<section id='adminstuff'>
<h2>Admin</h2>
<ul>
<li><a target="_blank" href="webstats.php">Web Stats</a></li>
<li><a target="_blank" href="http://bartonphillips.dyndns.org/apc.php">UPS</a></li>
<li><a target="_blank" href="gitinfo.php">GitInfo</a></li>
<li><a target="_blank" href="gitstatus.php">GitStatusAll</a></li>
<li><a target="_blank" href="http://www.bartonphillips.dyndns.org">Rpi</a></li>
<li><a target="_blank" href="http://www.bartonphillips.dyndns.org:5080">Rpi2</a></li>
<li><a target="_blank" href="http://www.bartonphillips.dyndns.org:4080">Hp-envy</a></li>
</ul>
</section>
EOF;
}

// use the Dom class to get the Sans '.diary h2' as text.
// This class is great for scrubing sites.

use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->load('https://isc.sans.edu/');
$sans = "<span class='sans'>". $dom->find(".diary h2 a")->text . "</span>";

// ***************
// Render the page
// ***************

echo <<<EOF
$top
<section id='browser-info'>
$hereMsg
<div class="locstr">
   Our domain is <i>bartonphillips.com</i><br/>
   $locstr
Start: <span class='green'>$date</span><br>
Today is: <span id="datetoday">$date</span></div>
<hr>
<p>
   This page is dynamically generated using PHP on our server at
   <a target="_blank" href="http://www.digitalocean.com/">DigitalOcean.com</a>.
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
<th><a target="_blank" href="http://www.granbyrotary.org"><button>The Granby Rotary Club</button></a></th>
<th><a target="_blank" href="http://www.applitec.com"><button>Applied Technology Resouces Inc.</button></a></th>
<th><a target="_blank" href="http://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a></th>
</tr>
<tr>
<th><a target="_blank" href="http://www.mountainmessiah.com"><button>Mountain Messiah</button></a></th>
<th><a target="_blank" href="http://www.bartonphillips.org"><button>bartonphillips.org</button></a></th>
<th><a target="_blank" href="http://www.bartonlp.com/toweewx.php"><button>My Weather Station</button></a></th>
</tr>
<tr>
<th><a target="_blank" href="http://www.bartonlp.com"><button>bartonlp.com</button></a></th>
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
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/bartonphillips">My GitHub sites</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/site-class/">SiteClass</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/updatesite/">UpdateSite Class</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/rssfeed/">RssFeed Class</a></li>
</ul>
</section>

<section id="interesting">
<h2>Interesting Sites</h2>
<ul>
<li><a target="_blank" href="http://www.wunderground.com/personal-weather-station/dashboard?ID=KCATHOUS54#history">
Weather Underground</a></li>
<li><a target="_blank" href="http://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="spacestation.php">Space Station Location</a></li>
<li><a target="_blank" href="proxy.php?http://www.swam.us">Southwest Aquatic Master</a></li>
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
<li><a target="_blank" href="http://www.bartonlp.com/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a target="_blank" href="http://www.bartonlp.org/pug-examples.php">Examples Using Pug</a>
<li><a target="_blank" href="javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a target="_blank" href="linuxmint-from-iso.php">How to Install Linux Mint via ISO from Disk</a></li>
<li><a target="_blank" href="testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a target="_blank" href="dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a target="_blank" href="localstorage.html">Local Storage Example: How To Resize An Image With JavaScript</a><br>
<li><a target="_blank" href="filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="easter-example.php">When is Easter and other holidays realted to Easter?</a><br>
<li><a target="_blank" href="urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="http://www.bartonlp.com/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="http://www.bartonlp.com/getIP.php">Check Ip Address</a></li>
<li><a target="_blank" href="https://wiki.amahi.org/index.php/Gmail_As_Relay_On_Ubuntu">
How to setup Linux Mint email via Gmail.com</a></li>
</ul>
</section>

<section id='projects'>
<h2>GitHub and PHPClasses Projects</h2>
 
<h3>PHP SiteClass Mini Framework</h3>
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
<p>The <b>SiteClass</b> framework is hosted at<br>
<a target="_blank" href="proxy.php?https://github.com/bartonlp/SiteClass">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a>
and also at
<a target="_blank"
href="proxy.php?http://www.phpclasses.org/package/9105-PHP-Create-database-driven-Web-sites.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>.
<br>Give it a try and let me know if you like it.</p>
<hr>

<h3>UpdateSite Class</h3>
<p>This class works with SiteClass. It lets you create sections or articles in a webpage that can be edited via the
web browser. The sections are stored in a database (MySql is prefered).</p>
<p>You can find my <b>UpdateSite Class</b> at<br>
<a target="_blank" href="proxy.php?https://github.com/bartonlp/updatesite">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a>
and also at 
<a target="_blank"
href="proxy.php?http://www.phpclasses.org/package/10042-PHP-Updateable-section-in-a-website-.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'></a>
and the <a target="_blank" href="https://bartonlp.github.io/updatesite">Documentation</a>.</p>
<hr>

<h3>PHP Slide Show Class</h3>

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
href="proxy.php?http://github.com/bartonlp/slideshow">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank"
href="proxy.php?http://www.phpclasses.org/browse/author/592640.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
alt="php classes logo" /></a></p>
<hr>

<h3>PHP MySql Slide Show Class</h3>

<p>This package can be used to present a slide show from images listed in a database.
The main class can retrieve lists of images to be displayed from a MySQL database table.</p>

<p>The class can also add or update the slideshow image lists in the database table,
The actual images can be stored on the filesystem or in the MySql table as base64 data.</p>
  
<p>You can find my <b>MySql Slide Show Class</b> at<br>
<a target="_blank" href="proxy.php?http://github.com/bartonlp/mysqlslideshow">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank" href="proxy.php?http://www.phpclasses.org/browse/author/592640.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>

<h3>RssFeed Class</h3>

<p>This package can read and get information from an RSS feed. It is simple to use.</p>
<p>You can find my <b>RssFeed Class</b> at<br>
<a target="_blank" href="proxy.php?http://github.com/bartonlp/rssfeed">GitHub
<img id="octocat" src="http://bartonphillips.net/images/Octocat.jpg"></a> and also at
<a target="_blank" href="proxy.php?https://www.phpclasses.org/package/10074-PHP-Read-RSS-feeds.html">
<img src="http://bartonphillips.net/images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>
<!-- # SANS Infocon Status -->
<div class="center">
<a target="_blank" href="proxy.php?https://isc.sans.org">
<img alt="Internet Storm Center Infocon Status"
src="http://bartonphillips.net/images/internetstorm-icon.gif">$sans</a>
</div>
</section>

$footer
EOF;

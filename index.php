<?php
// Main page for bartonphillips.com
// recapcha site key: 6LefxlMnAAAAALcjQAYEBYCOhBXpLDGEL0Q8NzMt
// recapcha secret key: 6LefxlMnAAAAAHWF6S3iofqztaqqiTFAwHfteHD6

$nonce = base64_encode(bin2hex(openssl_random_pseudo_bytes(8)));
header("Content-Security-Policy: 
    default-src 'self' https://bartonlp.com/otherpages https://bartonphillips.net https://code.jquery.com; 
    connect-src 'self' https://bartonlp.com/otherpages/js https://bartonlp.com/otherpages https://bartonlp.com/otherpages/beacon.php https://bartonlp.com/otherpages/tracker.php https://bartonlp.com/otherpages/geoAjax.php https://bartonphillips.net https://code.jquery.com https://maps.googleapis.com;
    script-src 'self' https://bartonlp.com/otherpages https://bartonphillips.net/js https://maps.googleapis.com https://code.jquery.com 'nonce-ZGE5NDdiMzY5ODc1ZThhNA=='; // 'unsafe-inline' 'unsafe-eval';
    img-src 'self' data: https://bartonphillips.net https://bartonlp.com/otherpages https://bartonlp.com/otherpages/tracker.php https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%203.svg;
    report-uri https://bartonlp/otherpages/cspreport2.php");

define('BLP_INDEX_VERSION', "BLP-index-1.1.0"); // BLP 2025-03-28 - 

$_site = require_once getenv("SITELOADNAME"); 
//$_site = require_once "/var/www/site-class/includes/autoload.php";

$S = new SiteClass($_site); // This must be changed if you use SimpleSiteClass.

$S->nonce = $nonce;

require_once "./index.i.php"; // Get the majority of the php

$S->msg = "PhpVersion: " . PHP_VERSION .
          "<br><a href='https://www.digitalocean.com/?refcode=b0cc31a0e083&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge'>".
          "<img nonce='$nonce' src='https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%203.svg' alt='DigitalOcean Referral Badge' /></a>".
          "<br>Version: " . BLP_INDEX_VERSION;

ob_start(); // Start output buffering
require "/var/www/composer.lock";
$x = ob_get_clean();

if(($n = preg_match("~\"url\": \"https://github.com/bartonlp/site-class.git\",\n *\"reference\": \"(.*?)\"~", $x, $m)) === false) {
  exit("index.php, preg_match returned false: ERROR");
}
$reporef = substr($m[1], 0, 7);

$S->msg1 = "<br>{$S->__toString()}={$S->getVersion()}, engine={$S->dbinfo->engine}<br>".
           "siteload=" . SITELOAD_VERSION . ", reporef=$reporef";
$S->title = "Barton Phillips";
$S->desc = "Interesting Things, About the Internet, Tips and Tutorials";
$S->link = <<<EOF
<link rel='stylesheet' href='/index.css'>
EOF;

$S->b_script = <<<EOF
  <script nunce='$nunce' src='https://bartonphillips.net/js/phpdate.js'></script>
  <script nunce='$nunce' src='https://bartonphillips.net/js/maps.js'></script>
  <script nunce='$nunce' src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6GtUwyWp3wnFH1iNkvdO9EO6ClRr_pWo&loading=async&callback=initMap&v=weekly" async></script>
  <script nunce='$nunce'>
// This was formaly index.js now it is inline
    
'use strict';

// If we have adminstuff we need another column.

if(window.CSS) {
  if(CSS.supports('display', 'grid') && $("#adminstuff").length != 0) {
    $("#grid-section").css("grid-template-columns", "repeat(4, 1fr)");
  }
}

// Local date/time for 'Today is' & 'Your Time is'. Uses phpdate.js
// loaded in index.php

setInterval(function() {
  var d = date("l F j, Y");
  var t = date("H:i:s T"); // from phpdate.js
  $("#datetoday").html("<span class='green'>"+
                       d+"</span><br>Your Time is: <span class='green'>"+
                       t+"</span>");
}, 1000);
  </script>    
EOF;

[$top, $footer] = $S->getPageTopBottom();

// Check If this is a high risk IP. Comes from index.i.php

if($istor->risk === "High") {
  echo <<<EOF
$top
<hr>
<h2>You are a High Risk BOT</h2>
<p>Nothing here to see.</p>
<hr>
$footer
EOF;

  error_log("index.php: id=$istor->id, ip=$istor->ip, site=$istor->site, page=$istor->page, High risk ip found via https://api-bdc.net");
  
  $sql = "insert into $S->masterdb.badplayer (id, ip, site, page, type, errno, errmsg, agent, created, lasttime) ".
  "values('$istor->id', '$istor->ip', '$istor->site', '$istor->page', 'HIGH RISK IP', '-999', 'High risk ip found', '$S->agent', now(), now())";

  $S->sql($sql);
  exit();
}

// ***************
// Render the page
// BLP 2021-09-22 -- $hereMsg is set in index.i.php along with $locstr, $adminstuff and $date
// ***************

echo <<<EOF
$top
<section id='browser-info'>
<!-- Either 'You have been here nn' or 'Welcome' with user name -->
$hereMsg
<p id="geomessage"></p>
<div class="locstr">
   Our domain is <i>bartonphillips.com</i><br/>
<!-- Location information if NOT a bot -->
   $locstr
Start: <span class='green'>$date in New Bern, NC</span><br>
Today is: <span id="datetoday">$date</span>
</div>

<hr>
<p>
   This page is dynamically generated using PHP on our server at
   <a target="_blank" href="https://www.digitalocean.com/">DigitalOcean.com</a>.
   Very little JavaScript is used in this page. We collect &quot;Google Analytics&quot, &quot;Google Maps&quot; geo-positioning data and fingerprint data.
   <a target="_blank" href="privacy.php">Privacy Statement</a>.
</p>

<p>
   <span class='red'>However</span>, some of the pages we link to do collect tracking information
   and COOKIES and make extensive use of JavaScript.
</p>
</section>

<!-- BLP 2021-03-25 - If we change this it will affect scraper-await-fetch.php -->
<section id="others">
<!-- BLP 2021-03-25 - end warning -->
<h2>Visit one of the other websites designed by Barton Phillips</h2>
<!-- Other Sites That I have made -->
<div id="otherSites" class="mylinks">
<a target="_blank" href="https://www.newbern-nc.info"><button>The Tyson Group</button></a>
<a target="_blank" href="https://www.newbernzig.com"><button>New Bern Zig</button></a>
<a target="_blank" href="https://www.jt-lawnservice.com"><button>JT Lawn Service</button></a>
<a target="_blank" href="https://www.swam.us"><button>Southwest Aquatic Master</button></a>
<a target="_blank" href="https://www.bartonlp.org"><button>bartonlp.org</button></a>
<a target="_blank" href="https://www.bonnieburch.com"><button>Bonnie's Home Page</button></a>
<a target="_blank" href="https://www.bartonphillips.org"><button>Home HP</button></a>
<a target="_blank" href="https://rpi.bartonphillips.org"><button>RPI</button></a>
<a target="_blank" href="articles/Stories.php"><button>My Stories</button></a>
</div>
</section>

<div id="grid-section">
<section id="github">
<h2 class="center">GitHub Projects</h2>
<ul>
<li><a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://bartonlp.github.io/bartonphillips.com">My GitHub sites</a></li>
<li><a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://bartonlp.github.io/site-class/">SiteClass</a></li>
<li><a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://bartonlp.github.io/updatesite/">UpdateSite Class</a></li>
<li><a target="_blank" href="https://bartonlp.com/otherpages/goto.php?blp=ingrid&https://bartonlp.github.io/rssfeed/">RssFeed Class</a></li>
</ul>
</section>

<!-- BLP 2021-03-25 - If we change this it will affect scraper-await-fetch.php -->
<section id="interesting">
<!-- BLP 2021-03-25 - End warning -->
<h2 class="center">Interesting Sites</h2>
<ul>
<li><a target="_blank" href="https://www.bnai-sholem.com">Temple B'nai Sholem</a></li>
<li><a target="_blank" href="https://newbernrotary.org">New Bern Breakfast Rotary Club</a></li>
<li><a target="_blank" href="https://www.wunderground.com/weather/us/nc/newbern/28560">Weather Underground</a></li>
<li><a target="_blank" href="https://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="https://www.littlejohnplumbing.com">Little John Plumbing</a></li>

</ul>
</section>

<!-- If it is me add adminstuff -->
$adminStuff
<section id="internet">
<h2 class="center">About the Internet</h2>
<ul>
<li><a target="_blank" href="articles/historyofinternet.php">History &amp; Timeline</a></li>
<li><a target="_blank" href="articles/howtheinternetworks.php">How It Works</a></li>
<li><a target="_blank" href="articles/howtowritehtml.php">How To Write HTML</a></li>
<li><a target="_blank" href="articles/buildawebsite.php">Build a Website</a></li>
</ul>
</section>
</div>

<section id="tips">
<h2>Useful Programs and Tutorials</h2>
<h3 class='subtitles'>Tutorials</h3>
<p class='subtitles'>Demo plus Source.</p>
<ul>
<li><a target="_blank" href="articles/javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a target="_blank" href="articles/promise.php">Use AJAX and Promise</a></li>
<li><a target="_blank" href="articles/fetch-promise.php">Use 'fetch' and Promise</a></li>
<li><a target="_blank" href="articles/async-await-2.php">Use 'async/await'</a></li>
<li><a target="_blank" href="articles/scraper-await-fetch.php">How To Scrape a Website</a></li>
<li><a target="_blank" href="/examples.js/user-test/worker.main.php">Demo using a Worker</a></li>
<li><a target="_blank" href="articles/linuxmint-from-iso.php">How to Install Linux via ISO from your hard drive</a></li>
<li><a target="_blank" href="articles/dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a target="_blank" href="articles/localstorage.php">Local Storage Example: How To Resize An Image With JavaScript</a></li>
<li><a target="_blank" href="articles/easter-example.php">When is Easter and other holidays related to Easter?</a></li>
<li><a target="_blank" href="articles/cssvariables.php">Use CSS var(--variable) to do 'hover' etc.</a></li>
<li><a target="_blank" href="/articles/filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="/examples.js/javascript-messages">Here are some interesting files that use popups</a></li>
</ul>

<h3 class='subtitles'>Useful Programs</h3>
<ul>
<li><a target="_blank" href="/articles/showCookies.php">Show the PHP and JavaScript Cookies</a></li>
<li><a target="_blank" href="/showmarkdown.php">Display <b>Markdown</b> files</a></li> <!-- needs to be in DOCROOT -->
<li><a target="_blank" href="/articles/base64.php">Decode Base 64</a></li>
<li><a target="_blank" href="/articles/urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="/articles/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="/articles/findinfofromip.php">Get More Info from IP Address</a></li>
<li><a target="_blank" href="/articles/iprangetocidr.php">Get the CIDR block given an IP range</a></li>
<li><a target="_blank" href="/articles/verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="/getIP.php">Check IP Address</a></li>
</ul>
</section>

<section id='projects'>
<a target="_blank" href='projects.php'>My GitHub and PHPClasses projects</a>
</section>

<!-- A place for the geo stuff -->

<div id="outer">
<div id="geocontainer"></div>
<button id="removemsg">Click to remove map image</button>
</div>

<hr>
$footer
EOF;

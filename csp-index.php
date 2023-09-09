<?php
// Main page for bartonphillips.com
// recapcha site key: 6LefxlMnAAAAALcjQAYEBYCOhBXpLDGEL0Q8NzMt
// recapcha secret key: 6LefxlMnAAAAAHWF6S3iofqztaqqiTFAwHfteHD6
// BLP 2023-09-07 - add a csp

$rand = base64_encode(bin2hex(openssl_random_pseudo_bytes(8)));

header("Content-Security-Policy: default-src 'none'; ".
       "script-src-elem 'self' 'unsafe-inline' ".
       "https://bartonlp.com/otherpages/ https://bartonphillips.net/ https://code.jquery.com/ https://openfpcdn.io/ https://maps.googleapis.com/; ".
       "style-src 'self' https://bartonphillips.net/css/ https://bartonlp.com/; ".
       "img-src data: https://bartonlp.com https://bartonphillips.net/; ".
       "connect-src https://bartonlp.com/otherpages/ https://maps.googleapis.com/; ".
       "font-src https://bartonphillips.net/; ".
       "script-src 'nonce-$rand' 'unsafe-eval'; ".
       "report-uri https://bartonphillips.com/examples.js/cspreport.php");

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

require_once("./index.i.php"); // Get the majority of the php

$S->msg = "PhpVersion: " . PHP_VERSION;
$S->msg1 = "<br>SiteClass Version: " . SITE_CLASS_VERSION;

// BLP 2023-09-07 - change title and add a banner for CSP
   
$S->title = "CSP Barton Phillips";
$S->banner = "<h1>CSP Version of bartonphillips.com/index.php</h1>";

$S->desc = "Interesting Things, About the Internet, Tips and Tutorials";
$S->link = <<<EOF
<link rel='stylesheet' href='/index.css'>
EOF;

// BLP 2023-09-07 - add nonce to script tags

$S->b_script = <<<EOF
  <script nonce='$rand' src='https://bartonphillips.net/js/phpdate.js'></script>
  <script nonce='$rand' src='/index.js'></script>
  <script nonce='$rand' src='https://bartonphillips.net/js/maps.js'></script>
  <script nonce='$rand' src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6GtUwyWp3wnFH1iNkvdO9EO6ClRr_pWo&callback=initMap&v=weekly" async></script>
EOF;

[$top, $footer] = $S->getPageTopBottom();

// Check if we have a static ip.
/* BLP 2023-08-11 - 
if($_SERVER['REMOTE_ADDR'] == '195.252.232.86') {
  $staticIp = <<<EOF
<a target="_blank" href="https://www.bartonphillips.org"><button>Home HP</button></a>
<a target="_blank" href="https://www.bartonphillips.com/fromrpi.php"><button>RPI</button></a>
EOF;
}
*/

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
   Very little JavaScript is used in this page. We collect &quot;Google Analitics&quot, &quot;Google Maps&quot; geo-positioning data, fingerprint data,
   and a COOKIE called 'mytime' which is used to tell how long some anonymous-someone has stayed on our site.
   <a target="_blank" href="privacy.php">Our privacy statement</a>.</p>
<p>
   <span class='red'>However</span>, some of the pages we link to do collect tracking information
   and COOKIES and make extensive use of JavaScript.
</p>
</section>

<!-- BLP 2021-03-25 - If we change this it will affect scraper-await-fetch.php -->
<section id="others">
<!-- BLP 2021-03-25 - end warning -->
<h2>Visit one of the other web sites designed by Barton Phillips</h2>
<!-- Other Sites That I have made -->
<div id="otherSites" class="mylinks">
<a target="_blank" href="https://www.bnai-sholem.com"><button>Temple B'nai Sholem</button></a>
<a target="_blank" href="https://newbernrotary.org"><button>New Bern Breakfast Rotary Club</button></a>
<a target="_blank" href="https://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a>
<a target="_blank" href="https://www.newbern-nc.info"><button>The Tyson Group</button></a>
<a target="_blank" href="https://www.newbernzig.com"><button>New Bern Zig</button></a>
<a target="_blank" href="https://www.jt-lawnservice.com"><button>JT Lawn Service</button></a>
<a target="_blank" href="https://www.swam.us"><button>Southwest Aquatic Master</button></a>
<a target="_blank" href="https://www.bartonlp.org"><button>bartonlp.org</button></a>
<a target="_blank" href="https://www.bonnieburch.com"><button>Bonnie's Home Page</button></a>
<!-- BLP 2023-08-11 - not doing this (add a \) \$staticIp -->
<a target="_blank" href="https://www.bartonphillips.org"><button>Home HP</button></a>
<a target="_blank" href="https://www.bartonphillips.com/fromrpi.php"><button>RPI</button></a>
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
<li><a target="_blank" href="https://www.wunderground.com/weather/us/nc/newbern/28560">Weather Underground</a></li>
<li><a target="_blank" href="https://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="https://developers.google.com/web/">Google/Web</a></li>
<li><a target="_blank" href="https://rivertownerentals.info/">Rivertowne Rentals</a></li>
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
<li><a target="_blank" href="/examples.js/javascript-messages">Here are some interesting files that use popups</a></li>
</ul>

<h3 class='subtitles'>Useful Programs</h3>
<ul>
<li><a target="_blank" href="/articles/showCookies.php">Show the PHP and JavaScript Cookies</a></li>
<li><a target="_blank" href="/showmarkdown.php">Display <b>Markdown</b> files</a></li> <!-- needs to be in DOCROOT -->
<li><a target="_blank" href="/articles/base64.php">Decode Base 64</a></li>
<li><a target="_blank" href="/articles/filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="/articles/urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="/articles/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="/articles/verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="/getIP.php">Check Ip Address</a></li>
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

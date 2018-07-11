<?php
// Main page for bartonphillips.com
// BLP 2018-03-06 -- Break this up into index.js, index.i.php and index.css
// BLP 2018-02-10 -- use cookie to determin if we show adminStuff
// BLP 2017-03-23 -- set up to work with https

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

require_once("index.i.php"); // Get the majority of the php

$h->title = $S->siteName;
$h->desc = "Weather Station, Interesting Things, About the Internet, Tips and Tutorials";
$h->banner = <<<EOF
<div id='mainTitle'>
$S->mainTitle<br>
<span>
<a target='_blank' href='https://www.bartonlp.com/toweewx.php'>My Home Weather Station</a>
</span><br>
<span><a target="_blank" href="aboutweewx.php">About My Weather Station</a></span>
</div>
EOF;

// link the index.css and rel=conanical
$h->link =<<<EOF
  <link rel="canonical" href="https://www.bartonphillips.com">
  <link rel='stylesheet' href='index.css'>
EOF;

// get phpdate.js and set the js doGit to $GIT

$h->script = <<<EOF
  <script src='https://bartonphillips.net/js/phpdate.js'></script>
  <script>var doGit = $GIT;</script>
  <script src='index.js'></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// ***************
// Render the page
// ***************

echo <<<EOF
$top
<!-- Internet Under Attack. Status message or NOTHING -->
$status
<section id='browser-info'>
<!-- Either 'You have been here nn' or 'Welcome' with user name -->
$hereMsg
<div class="locstr">
   Our domain is <i>bartonphillips.com</i><br/>
<!-- Location information if NOT a bot -->
   $locstr
Start: <span class='green'>$date in Goldsboro, NC</span><br>
Today is: <span id="datetoday">$date</span>
</div>
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
<a target="_blank" href="proxy.php?https://bartonplp.blogspot.com">
<button>
My BLOG<br>
Tips and Tricks
</button>
</a>
</section>

<section id="mysites">
<h2 class="center">Visit one of the other web sites designed by Barton Phillips</h2>

<!-- Small Screens -->
<div id="smallScreen">
<a target="_blank" href="https://www.granbyrotary.org"><button>The Granby Rotary Club</button></a>
<a target="_blank" href="https://www.applitec.com"><button>Applied Technology Resouces Inc.</button></a>
<a target="_blank" href="https://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a>
<a target="_blank" href="https://www.mountainmessiah.com"><button>Mountain Messiah</button></a>
<a target="_blank" href="https://www.bartonphillips.org/myhomepage"><button>bartonphillips.org</button></a>
<a target="_blank" href="https://www.bartonlp.com/toweewx.php"><button>My Weather Station</button></a>
<a target="_blank" href="https://www.bartonlp.com"><button>bartonlp.com</button></a>
<a target="_blank" href="https://www.bartonlp.org"><button>bartonlp.org</button></a>
<a target="_blank" href="https://mynode.bartonlp.org"><button>My node.js Page</button></a>
</div>

<!-- Large Screens -->
<table id="bigScreen" class="mylinks">
<tbody>
<tr>
<th><a target="_blank" href="https://www.granbyrotary.org">The Granby Rotary Club</a></th>
<th><a target="_blank" href="https://www.applitec.com">Applied Technology Resouces Inc.</a></th>
<th><a target="_blank" href="https://www.allnaturalcleaningcompany.com">All Natural Cleaning</a></th>
</tr>
<tr>
<th><a target="_blank" href="https://www.mountainmessiah.com">Mountain Messiah</a></th>
<th><a target="_blank" href="https://www.bartonphillips.org/myhomepage">bartonphillips.org</a></th>
<th><a target="_blank" href="https://www.bartonlp.com/toweewx.php">My Weather Station</a></th>
</tr>
<tr>
<th><a target="_blank" href="https://www.bartonlp.com">bartonlp.com</a></th>
<th><a target="_blank" href="https://www.bartonlp.org">bartonlp.org</a></th>
<th><a target="_blank" href="https://mynode.bartonlp.org">My node.js Page</a></th>
</tr>
</tbody>
</table>
</section>

<div id="grid-section">
<section id="github">
<h2 class="center">GitHub Projects</h2>
<ul>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/bartonphillips.com">My GitHub sites</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/site-class/">SiteClass</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/updatesite/">UpdateSite Class</a></li>
<li><a target="_blank" href="proxy.php?https://bartonlp.github.io/rssfeed/">RssFeed Class</a></li>
</ul>
</section>

<section id="interesting">
<h2 class="center">Interesting Sites</h2>
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
<li><a target="_blank" href="https://www.bartonlp.org/pug-examples.php">Examples Using Pug</a>
<li><a target="_blank" href="articles/javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a target="_blank" href="articles/promise.php">Use AJAX and Promise</a></li>
<li><a target="_blank" href="articles/fetch-promise.php">Use 'fetch' and Promise</a></li>
<li><a target="_blank" href="articles/async-await-2.php">Use 'async/await'</a></li>
<li><a target="_blank" href="articles/scraper-await-fetch.php">How To Scrape a Websites</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/examples.js/user-test/worker.main.php">Demo using a Worker</a></li>
<li><a target="_blank" href="articles/linuxmint-from-iso.php">How to Install Linux Mint via ISO from Disk</a></li>
<li><a target="_blank" href="articles/dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a target="_blank" href="articles/localstorage.php">Local Storage Example: How To Resize An Image With JavaScript</a><br>
<li><a target="_blank" href="articles/easter-example.php">When is Easter and other holidays realted to Easter?</a><br>
</ul>

<h3 class='subtitles'>Useful Programs</h3>
<ul>
<li><a target="_blank" href="https://www.bartonlp.com/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/base64.php">Decode Base 64</a></li>
<li><a target="_blank" href="articles/testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a target="_blank" href="articles/filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="articles/urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="https://www.bartonlp.com/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="articles/verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="https://www.bartonlp.com/getIP.php">Check Ip Address</a></li>
<li><a target="_blank" href="https://wiki.amahi.org/index.php/Gmail_As_Relay_On_Ubuntu">
How to setup Linux Mint email via Gmail.com</a></li>
</ul>
</section>

<section id='projects'>
<a target="_blank" href='projects.php'>My GitHub and PHPClasses projects</a>
</section>
<!-- Stormwatch isc.sans.edu -->
$stormwatchpage
<hr>
$footer
EOF;

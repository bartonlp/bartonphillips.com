<?php
// Main page for bartonphillips.com
// BLP 2018-03-06 -- Break this up into index.js, index.i.php and index.css
// BLP 2018-02-10 -- use cookie to determin if we show adminStuff
// BLP 2017-03-23 -- set up to work with https

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

require_once("index.i.php"); // Get the majority of the php

$h->title = $S->siteName;
$h->desc = "Interesting Things, About the Internet, Tips and Tutorials";
$h->banner = <<<EOF
<div id='mainTitle'>
$S->mainTitle<br>
</div>
EOF;

// link the index.css and rel=conanical
$h->link =<<<EOF
  <link rel="canonical" href="https://www.bartonphillips.com">
  <link rel='stylesheet' href='index.css'>
EOF;

// get phpdate.js and set the js doGit to $GIT

$b->script = <<<EOF
  <script src='https://bartonphillips.net/js/phpdate.js'></script>
  <script>var doGit = $GIT;</script>
  <script src='index.js'></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, $b);

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
Start: <span class='green'>$date in New Bern, NC</span><br>
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

<section id="others">
<h2>Visit one of the other web sites designed by Barton Phillips</h2>

<!-- Other Sites That I have made -->
<div id="otherSites" class="mylinks">
<a target="_blank" href="http://www.bnai-sholem.com"><button>Temple B'nai Sholem</button></a>
<a target="_blank" href="https://newbernrotary.org"><button>New Bern Breakfast Rotary Club</button></a>
<a target="_blank" href="https://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a>
<a target="_blank" href="https://www.newbern-nc.info"><button>The Tyson Group</button></a>
<a target="_blank" href="https://www.bartonlp.org"><button>bartonlp.org</button></a>
</div>
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
<li><a target="_blank" href=https://www.wunderground.com/weather/us/nc/newbern/28560">Weather Underground</a></li>
<li><a target="_blank" href="http://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a target="_blank" href="http://www.bartonphillips.com/spacestation.php">Space Station Location</a></li>
<li><a target="_blank" href="proxy.php?http://www.swam.us">Southwest Aquatic Master</a></li>
<li><a target="_blank" href="http://www.computerscienceonline.org">Computer Science Online</a></li>
<li><a target="_blank" href="https://developers.google.com/web/">Google/Web</a></li>
<li><a target="_blank" href="https://www.frontierinternet.com/gateway/data-storage-timeline/">Storage System Timeline</a></li>
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
<!--<li><a target="_blank" href="https://www.bartonlp.org/pug-examples.php">Examples Using Pug</a>-->
<li><a target="_blank" href="articles/javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a target="_blank" href="articles/promise.php">Use AJAX and Promise</a></li>
<li><a target="_blank" href="articles/fetch-promise.php">Use 'fetch' and Promise</a></li>
<li><a target="_blank" href="articles/async-await-2.php">Use 'async/await'</a></li>
<li><a target="_blank" href="articles/scraper-await-fetch.php">How To Scrape a Website</a></li>
<li><a target="_blank" href="bartonlp/examples.js/user-test/worker.main.php">Demo using a Worker</a></li>
<li><a target="_blank" href="articles/linuxmint-from-iso.php">How to Install Linux via ISO from your hard drive</a></li>
<li><a target="_blank" href="articles/dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a target="_blank" href="articles/localstorage.php">Local Storage Example: How To Resize An Image With JavaScript</a><br>
<li><a target="_blank" href="articles/easter-example.php">When is Easter and other holidays related to Easter?</a><br>
</ul>

<h3 class='subtitles'>Useful Programs</h3>
<ul>
<!-- Get Markdown from our DEFAULT server -->
<li><a target="_blank" href="https://bartonlp.org/bartonlp/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a target="_blank" href="bartonlp/base64.php">Decode Base 64</a></li>
<li><a target="_blank" href="articles/testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a target="_blank" href="articles/filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a target="_blank" href="articles/urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a target="_blank" href="bartonlp/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a target="_blank" href="articles/verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="bartonlp/getIP.php">Check Ip Address</a></li>
</ul>
</section>

<section id='projects'>
<a target="_blank" href='projects.php'>My GitHub and PHPClasses projects</a>
</section>
<!-- Stormwatch isc.sans.edu -->
<!-- $stormwatchpage -->
<hr>
$footer
EOF;

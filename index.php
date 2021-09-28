<?php
// Main page for bartonphillips.com
// BLP 2021-09-21 -- Update the registration process. See index.i.php and register.php
// BLP 2021-03-26 -- set doGit in the bottom script to blank so we don't do the notify. We don't
// want to force $GIT to blank because it is used in adminstuff.php to show that something has
// changed.
// BLP 2021-03-24 -- remove 'target="_blank"' from all links
// BLP 2018-03-06 -- Break this up into index.js, index.i.php and index.css
// BLP 2018-02-10 -- use cookie to determin if we show adminStuff
// BLP 2017-03-23 -- set up to work with https

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

require_once("index.i.php"); // Get the majority of the php

$h->title = $S->siteName;
$h->desc = "Interesting Things, About the Internet, Tips and Tutorials";

// I am using the mainTitle in mysitemap.json

// link the index.css and rel=conanical
$h->link =<<<EOF
  <link rel="canonical" href="https://www.bartonphillips.com">
  <link rel='stylesheet' href='/index.css'>
EOF;

// get phpdate.js and set the js doGit to $GIT
// This goes at the bottom.

$b->script = <<<EOF
  <script src='https://bartonphillips.net/js/phpdate.js'></script>
  <!--<script>var doGit = '$GIT';</script>-->
  <script>var doGit = ''; // don't do this</script>
  <script src='index.js'></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, $b);

// ***************
// Render the page
// BLP 2021-09-22 -- $hereMsg is set in index.i.php along with $locstr, $adminstuff and $date
// ***************

echo <<<EOF
$top
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
   <a href="https://www.digitalocean.com/">DigitalOcean.com</a>.
   Very little JavaScript is used in this page. We only collect &quot;Google Analitics&quot; COOKIES and
   a COOKIE called 'mytime' which is used to tell how long some anonymous someone has stayed on our site.
   We don't track you.
   We do collect anonymous information for page counting and analysis only. <a href="privacy.php">Our privacy statement</a>.</p>
<p>
   <span class='red'>However</span>, some of the pages we link to do collect tracking information
   and COOKIES and make extensive use of JavaScript.
</p>
</section>

<!-- BLP 2021-03-25 -- If we change this it will affect scraper-await-fetch.php -->
<section id="others">
<!-- BLP 2021-03-25 -- end warning -->
<h2>Visit one of the other web sites designed by Barton Phillips</h2>
<!-- Other Sites That I have made -->
<div id="otherSites" class="mylinks">
<a href="https://www.bnai-sholem.com"><button>Temple B'nai Sholem</button></a>
<a href="https://newbernrotary.org"><button>New Bern Breakfast Rotary Club</button></a>
<a href="https://www.allnaturalcleaningcompany.com"><button>All Natural Cleaning</button></a>
<a href="https://www.newbern-nc.info"><button>The Tyson Group</button></a>
<a href="https://www.newbernzig.com"><button>New Bern Zig</button></a>
<a href="https://www.bartonlp.org"><button>bartonlp.org</button></a>
</div>
</section>

<div id="grid-section">
<section id="github">
<h2 class="center">GitHub Projects</h2>
<ul>
<li><a href="goto.php?blp=ingrid&https://bartonlp.github.io/bartonphillips.com">My GitHub sites</a></li>
<li><a href="goto.php?blp=ingrid&https://bartonlp.github.io/site-class/">SiteClass</a></li>
<li><a href="goto.php?blp=ingrid&https://bartonlp.github.io/updatesite/">UpdateSite Class</a></li>
<li><a href="goto.php?blp=ingrid&https://bartonlp.github.io/rssfeed/">RssFeed Class</a></li>
</ul>
</section>

<!-- BLP 2021-03-25 -- If we change this it will affect scraper-await-fetch.php -->
<section id="interesting">
<!-- BLP 2021-03-25 -- End warning -->
<h2 class="center">Interesting Sites</h2>
<ul>
<li><a href="https://www.wunderground.com/weather/us/nc/newbern/28560">Weather Underground</a></li>
<li><a href="https://www.raspberrypi.org/">RaspberryPi</a></li>
<li><a href="goto.php?blp=ingrid&http://www.swam.us">Southwest Aquatic Master</a></li>
<li><a href="https://www.computerscienceonline.org">Computer Science Online</a></li>
<li><a href="https://developers.google.com/web/">Google/Web</a></li>
<li><a href="https://www.frontierinternet.com/gateway/data-storage-timeline/">Storage System Timeline</a></li>
<li><a href="https://rivertownerentals.info/">Rivertowne Rentals</a></li>
</ul>
</section>

<!-- If it is me add adminstuff -->
$adminStuff
<section id="internet">
<h2 class="center">About the Internet</h2>
<ul>
<li><a href="articles/historyofinternet.php">History &amp; Timeline</a></li>
<li><a href="articles/howtheinternetworks.php">How It Works</a></li>
<li><a href="articles/howtowritehtml.php">How To Write HTML</a></li>
<li><a href="articles/buildawebsite.php">Build a Website</a></li>
</ul>
</section>
</div>

<section id="tips">
<h2>Useful Programs and Tutorials</h2>
<h3 class='subtitles'>Tutorials</h3>
<p class='subtitles'>Demo plus Source.</p>
<ul>
<li><a href="articles/javascript-siteclass.php">Create a JavaScript Only Site</a></li>
<li><a href="articles/promise.php">Use AJAX and Promise</a></li>
<li><a href="articles/fetch-promise.php">Use 'fetch' and Promise</a></li>
<li><a href="articles/async-await-2.php">Use 'async/await'</a></li>
<li><a href="articles/scraper-await-fetch.php">How To Scrape a Website</a></li>
<li><a href="/examples.js/user-test/worker.main.php">Demo using a Worker</a></li>
<li><a href="articles/linuxmint-from-iso.php">How to Install Linux via ISO from your hard drive</a></li>
<li><a href="articles/dynamicscript.php">Dynamically create script tags and IFRAMES using PHP or JavaScript</a></li>
<li><a href="articles/localstorage.php">Local Storage Example: How To Resize An Image With JavaScript</a></li>
<li><a href="articles/easter-example.php">When is Easter and other holidays related to Easter?</a></li>
<li><a href="articles/cssvariables.php">Use CSS var(--variable) to do 'hover' etc.</a></li>
</ul>

<h3 class='subtitles'>Useful Programs</h3>
<ul>
<li><a href="/showmarkdown.php">Display <b>Markdown</b> files</a></li>
<li><a href="/base64.php">Decode Base 64</a></li>
<li><a href="articles/testmodernizer.php">What Features does Your Browser Have</a></li>
<li><a href="articles/filereader.php">Using the File interface (File, FileReader, FileList, Blob)</a></li>
<li><a href="articles/urlcountrycodes.php">Find the country given a url country code</a><br>
<li><a href="/getcountryfromip.php">Get Country from IP Address</a></li>
<li><a href="articles/verifyemailaddress.php">Verify Email Address</a></li>
<li><a href="/getIP.php">Check Ip Address</a></li>
</ul>
</section>

<section id='projects'>
<a href='projects.php'>My GitHub and PHPClasses projects</a>
</section>
<hr>
$footer
EOF;

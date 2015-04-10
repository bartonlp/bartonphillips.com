<?php
// Main page for bartonphillips.com
// BLP 2015-01-26 -- now served from digitalocean.com
// BLP 2015-01-10 -- reworked to use sections and put all style
// in css rather than inline!
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp; // takes an array if you want to change defaults

// css/blp.css is included in head.i.php

$h->css = <<<EOF
  <!-- Local CSS -->
  <style>
#browser-info {
  border-top: 1px solid gray;
}
#blog {
  width: 50%;
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
@media (max-width: 600px) {
  a {
    font-size: 1.375rem; /* rems so we don't get relitive em experience */
    line-height: 2.5rem;
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

  $("a[href='toweewx.php']").click(function() {
    // when toweewx clicked add t= Unix date to the href attribute 
    var d = date("U");
    $(this).attr("href", "toweewx.php?t="+d);
  });

  $("a[href='webstats-new.php']").click(function() {
    var d = date("U");
    $(this).attr("href", "webstats-new.php?t="+d);
  });

  $("#weewx").click(function() {
    var we = $(this).attr('href');
    we = we.replace(/xx/, weewx);
    we = we.replace(/\?.*/,'');
    $(this).attr('href', we+"?"+Math.random());
    return true;
  });

  $("#apcups").click(function() {
    var we = $(this).attr('href');
    we = we.replace(/xx/, weewx);
    we = we.replace(/\?.*/,'');
    $(this).attr('href', we+"?"+Math.random());
    return true;
  });

  function set_test(name,status){
    weewx = status ? '192.168.0.6' : '192.168.0.4/html';
    //console.log("weewx: ", weewx);
    //var el=document.getElementById(name+'_test');
    //el.innerHTML=status?'Yes, you are logged in':'No, you\'re not logged in';
    //el.style.color=status?'#0a0':'#a00';
    //el.style.fontWeight='bold';
  }
  (function(){
    //var test_test=document.getElementById('test_test');
    //test_test.innerHTML='Checking...';
    var img = document.createElement('img');
    img.src ='http://192.168.0.6/phpbuilder.gif?'+Math.random();
    img.onload = function() {
      set_test('test',1);
    };
    img.onerror = function() {
      set_test('test',0);
    };
    //img.style.display = 'none';
    //document.body.appendChild(img);
  })();
});
  </script>  
EOF;

$h->title = "Barton Phillips Home Page";
$h->banner = <<<EOF
<div class='center'>
<h1>The New Barton Phillips Home Page</h1>
<h2>
<a target='_blank' href='toweewx.php'>My Home Weather Station</a>
</h2>
<h3><a href="aboutweewx.php">About My Weather Station</a></h3>
</div>
EOF;

$ref = $_SERVER['HTTP_REFERER'];

if($ref) {
  if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
  $ref =<<<EOF
You came to this site from <i>$ref</i>.<br>
EOF;
}

// BLP 2014-10-24 -- changed blp flag to 7098
// BLP 2014-08-18 -- add blp=8653 as flag
// If it's me add in the admin stuff

if($S->isBlp() || ($_GET['blp'] == "7098")) {
  // BLP 2014-12-02 -- as this is only for admin (me) I am using
  // my local net address 
  
  $adminStuff = <<<EOF
<h2>Administration Links</h2>
<ul>
<li><a target="_blank" href="http://webmail.bartonlp.com">WEB Mail for bartonlp.com</a></li>
<li><a target="_blank" id="weewx" href="http://xx/weewx">WEEWX home</a></li>
<li><a target="_blank" id="apcups" href="http://xx/apc.php">APC Status home</a></li>
<li><a target="_blnak" href="http://www.applitec.com/glencabin">Glen's Cabin</a></li>
<li><a target="_blank" href="https://www.adafruit.com/">Adafruit</a></li>
</ul>
EOF;
}

list($top, $footer) = $S->getPageTopBottom($h, array('msg1'=>"<hr>"));

$ip = $S->ip;
$blpIp = $S->blpIp;

$curdate = date("Y-m-d");

// Get todays count and visitors from daycounts table
$S->query("select sum(count) as count, sum(visits) as visits from daycounts where date='$curdate'");
$row = $S->fetchrow();
$count = number_format($row['count'], 0, "", ",");
$visits = number_format($row['visits'], 0, "", ",");

// Get total number for today.
$S->query("select count(*) from daycounts where date='$curdate'");
list($visitors) = $S->fetchrow();
$visitors = number_format($visitors, 0, "", ",");

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
   Your IP Address: <i>$S->ip</i><br/>
   Today is: <span id="datetoday">$date</span></p>
   <hr>
   <p>This page is dynamically generated using PHP on our server at
   <a target="_blank" href="http://www.digitalocean.com/">DigitalOcean.com</a>.
   Almost no JavaScript is used in this page. We collect no COOKIES. We don't track you.
   We do collect anonymous information for page counting etc. However, some of the
   pages we link to do collect tracking information and COOKIES and make extensive use
   of JavaScript.
</p> 
</section>

<section id="blog">
<a target="_blank" href="blp-blog.php">My BLOG with tips and tricks</a>.
Leave a comment or feedback about this site.
</section>

<section id="links">
<h2>Visit one of the other web sites designed by Barton Phillips</h2>
<ul>
<li><a target="_blank" href="http://www.granbyrotary.org">The Granby Rotary Club</a></li>
<li><a target="_blank" href="http://www.endpolio.com">Rotary District 5450 End Polio Campaign</a></li>
<li><a target="_blank" href="http://www.grandlakerotary.org">The Grand Lake Rotary Club</a></li>
<li><a target="_blank" href="http://www.kremmlingrotary.org">The Kremmling Rotary Club</a></li>
<li><a target="_blank" href="pokerclub/">The Granby Monday Night Poker Group</a></li>
<li><a target="_blank" href="http://www.grandchorale.org">The Grand Chorale</a></li>
<li><a target="_blank" href="http://www.applitec.com">Applied Technology Resouces Inc.</a></li>
<li><a target="_blank" href="http://www.swam.us">South West Aquatic Masters</a></li>
<li><a target="_blank" href="http://www.tinapurwininsurance.com">Tina Purwin Insurance</a></li>
<li><a target="_blank" href="http://www.mountainmessiah.com">Mountain Messiah Sing Along</a><br>
<li><a target="_blank" href="toweewx.php">My Home Weather Station</a><br>
<li><a target="_blank" href="http://www.myphotochannel.com">www.MyPhotoChannel.com</a><br>
<li><a target="_blank" href="http://go.myphotochannel.com/">MyPhotoChannel 1and1</a> only a super user</li>
<li><a target="_blnak" href="http://www.puppiesnmore.com">PuppiesNmore</a></li>
<li><a target="_blank" href="http://www.puppiesnmore.com/cms">PuppiesNmore CMS</a></li>
<li><a target="_blank" href="http://www.bartonlp.com">bartonlp.com, Expermental Site</a></li>
<li><a target="_blank" href="http://gitHub.bartonphillips.com">Barton Phillips GitHub site</a></li>
<li><a target="_blank" href="webstats-new.php">Web Stats</a></li>

</ul>
$adminStuff
<h2>Links to Local Information Sites</h2>
<ul>
<li><a target="_blank" href="spacestation.php">ISS Overhead Times</a></li>
<li><a target="_blank" href="http://www.fs.usda.gov/arp">USDA Forest Service ARP</a></li>
<li><a target="_blank" href="http://www.gcwg.org">Grand County Wilderness Group</a></li>
<li><a target="_blank" href="http://www.rotary.org">Rotary International</a></li>
<li><a target="_blank" href="http://www.rotary5450.org">Rotary Distrct 5450</a></li>
<li><a target="_blank" href="http://www.granbychamber.com">Granby Chamber of Commerce</a></li>
<li><a target="_blank" href="http://www.granbyranch.com">Granby Ranch</a></li>
<li><a target="_blank" href="http://grand-countyconcertseries.org">Grand County Concert Series</a></li>
<li><a target="_blank" href="http://www.grandnordic.org">Grand Nordic</a> Cross Country Skiing</li>
<li><a target="_blank" href="http://www.wunderground.com/cgi-bin/findweather/getForecast?query=80446">Weather Unerground
   Granby</a></li>
</ul>
<h2>Interesting High Tech Sites</h2>
<ul>
<li><a target="_blank" href="https://dashboard.opendns.com/">OpenDNS</a></li>
<li><a target="_blank" href="http://www.html5rocks.com/en/">HTML5 Rocks</a></li>
<li><a target="_blank" href="http://www.sitepoint.com">Site Point</a></li>
<li><a target="_blank" href="http://www.raspberrypi.org/">RaspberryPi</a></li>
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
<li><a target="_blank" href="featurescheck.php">Browser Features by Agents</a></li>
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
<li><a target="_blank" href="verifyemailaddress.php">Verify Email Address</a></li>
<li><a target="_blank" href="http://checkip.dyndns.com/">Check Ip Address</a></li>
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
<p>The framework is hosted at <a href="https://github.com/bartonlp/SiteClass">GitHub
<img id="octocat" src="http://bartonlp.com/html/images/Octocat.jpg"></a>.
Give it a try and let me know if you like it.</p>
<hr>

<h2>PHP Slide Show Class</h2>
<p>You can find a <b>Slide Show Class</b> that I wrote on
<a target="_blank" href="http://www.phpclasses.org/browse/author/592640.html">
<img src="images/phpclasses-logo.gif" width='180' height='59'
alt="php classes logo" /></a></p>
<hr/>
<h2>PHP MySql Slide Show Class</h2>
<p>You can find a <b>MySql Slide Show Class</b> that I wrote on
<a target="_blank" href="http://www.phpclasses.org/browse/author/592640.html">
<img src="images/phpclasses-logo.gif" width='180' height='59'
 alt="php classes logo" /></a></p>
<hr>

<!-- # SANS Infocon Status -->
<div>
<p>
<a target="_blank" href="https://isc.sans.org">
<img width="354" height="92" alt="Internet Storm Center Infocon Status"
src="http://isc.sans.edu/images/status.gif" />
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

<?php
// This file will not be in any links or in the robots.txt file so no one should
// be able to find it.
// Anyone that does open this file will be tracked.

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp;

$h->title = "Directory";
$h->banner = "<h1>Directory</h1>";
list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

// Send me an email
$ip = $S->ip;
$agent = $S->agent;

$info = <<<EOF
Someone has accessed Directory.php. Info:
ip=$ip
agent=$agent
EOF;
  
mail("bartonphillips@gmail.com", "Directory.php Accessed?",
     $info,
     "From: Directory.php", "-f bartonphillips@gmail.com");

echo <<<EOF
$top
<hr/>
<p>
   Our domains are <i>bartonphillips.org</i> and <i>bartonphillips.com</i><br/>
   You got here via <i>{$_SERVER['SERVER_NAME']}</i>.
</p> 

<p style='clear: both'>
   Visit one of the other web sites designed by Barton Phillips</p>
<p>
<a target="_blank" href="http://www.granbyrotary.org">The Granby Rotary Club</a><br/>
<a target="_blank" href="http://www.endpolio.com">Rotary District 5450 End Polio Campaign</a><br/>
<a target="_blank" href="http://www.grandlakerotary.org">The Grand Lake Rotary Club</a><br/>
<a target="_blank" href="http://www.kremmlingrotary.org">The Kremmling Rotary Club</a><br/>
<a target="_blank" href="pokerclub/">The Granby Monday Night Poker Group</a><br/>
<a target="_blank" href="http://www.grandchorale.org">The Grand Chorale</a><br/>
<a target="_blank" href="http://www.applitec.com">Applied Technology Resouces Inc.</a><br/>
<a target="_blank" href="http://www.swam.us">South West Aquatic Masters</a><br/>
<a target="_blank" href="http://www.tinapurwininsurance.com">Tina Purwin Insurance</a><br/>
<a target="_blank" href="http://www.granbyranchnews.com">Granby Ranch News</a> will be off the air until 2012<br/>
<a target="_blank" href="http://www.mountainmessiah.com">Mountain Messiah Sing Along</a><br>
<a target="_blank" href="http://www.humanaspect.com">Human Aspect</a><br>
<a target="_blank" href="http://bartonphillips.dyndns.org/weewx">My Home Weather Station</a><br>
</p>
<p>Links to other sites</p>
<p>
<a target="_blank" href="http://www.gcwg.org">Grand County Wilderness Group</a><br/>
<a target="_blank" href="http://www.rotary.org">Rotary International</a><br/>
<a target="_blank" href="http://www.rotary5450.org">Rotary Distrct 5450</a><br/>
<a target="_blank" href="http://www.granbychamber.com">Granby Chamber of Commerce</a><br/>
<a target="_blank" href="http://www.granbyranch.com">Granby Ranch</a><br/>
<a target="_blank" href="http://grand-countyconcertseries.org">Grand County Concert Series</a><br/>
<a target="_blank" href="http://www.grandnordic.org">Grand Nordic</a> Cross Country Skiing<br/>
<a target="_blank" href="http://www.wunderground.com/cgi-bin/findweather/getForecast?query=80446">Weather Unerground
   Granby</a><br/>
</p>
<p>Helpful tips:<br/>
<a href="mx330.php">How To Setup The Canon MX330 All-In-One Print/Scan/Copy/Fax For Linux</a><br/>
<a href="howtowritehtml.php">Tutorial: How To Write HTML</a><br/>
<a href="usinghosts.php">Why can't I access my home-hosted website from my own computer</a>? This is a common problem.<br/>
<a href="easter-example.php">When is Easter and other holidays realted to Easter?</a><br>
<a href="http://www.phys.uu.nl/~vgent/easter/eastercalculator.htm">Site with lots of Easter and Passover Information</a><br> 
<a href="urlcountrycodes.php">Find the country give a url country code</a><br>
</p>

$footer
EOF;
?>
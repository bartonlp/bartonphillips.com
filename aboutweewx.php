<?php
// BLP 2015-03-31 -- About my weather station.

$_site = require_once(getenv("SITELOAD")."/siteload.php");

$S = new $_site->className($_site);

$h->title = "About Weather Station";
$h->banner = "<h1>About My Weather Station</h1>";
$h->css =<<<EOF
  <!-- local css -->
  <style>
.photo {
  width: 650px;
}
@media (max-width: 700px) {
  .photo {
    width: 300px;
  }
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<!--<img class='photo' src='/weewx-images/CIMG0076.JPG' alt="Davis Vantage Vue"><br>-->
<img class='photo' src='http://bartonphillips.net/images/weewx-images/CIMG0002.JPG' alt="Davis Vantage Vue Console">
<p>
I have had a Weather Station for the past 20 years. First at our
home in Canoga Park, CA, then at our home in Granby Ranch, CO., then in Newbury Park, CA. and now
in New Bern, NC.
My first weather station was an Origon Scientific which I puchased
in the early 1990's. When we moved to Granby Ranch, in the high
mountains, I bought a
<a href="http://www.davis.com/Category/Davis_Instruments_Vantage_Vue_Wireless_Weather_Station/59174?referred_id=20948&gclid=Cj0KEQiA6JemBRC5tYLRwYGcwosBEiQANA3IB0U22-9m8dRlzQCt5kVJp-ORsTKknZfDdOK204-IBmoaAjNt8P8HAQ">
Davis Instruments Vantage Vue Wireless Weather Station</a>. I changed
my station from the Origon Scientific to Davis because we lived
at 8,400 feet above sea level and the Origon was not rated for such
high altitude.</p>

<p>
The Vantage Vue has provided very satisfoatory results. I looked
around for weather station software to run on my Linux computers and
found <a href="http://www.weewx.com/">Weewx</a>. The software is
written in Python and the website has very good documentation and
support. As with most thing Linux the weather station software is
free and open source.</p>

<!--<img class='photo' src="http://bartonphillips.net/images/weewx-images/CIMG0077.JPG" alt="Davis Vantage Vue">-->

<p>
I installed the Davis equipment on my back patio in Colorado. The system is very
easy to setup and I had it up and running within a couple of hours.
The weewx software was very easy to install and configure and I had
it all working the same day I received the Davis equipment. Since then I have had it in Newbury Park, CA
and now on my patio in New Bern, NC.</p>

<p>
I originally had the software running on my home computer,
at the time a Dell 530, which I bought because Dell was
offering systems with Linux installed
(unfortunatly Dell stopped offering Linux and I stopped buying Dell).
After 10 years the Dell died and I wasn't able to fix it.
I bought an HP Envy, which was a nice upgrade.
I didn't want to use this new system as a server
because I wanted to be able to turn it on and off and experment with
different operationg systems etc.</p>

<p>
<img class='photo' src='http://bartonphillips.net/images/weewx-images/CIMG0003.JPG' alt="Raspberry PI"><br>
I bought a <a href="http://www.raspberrypi.org/">Raspberry PI</a>
which is a small low power fanless system. It sells for around $40 and uses very
little electricity. The PI has an ARM cpu but runs a version of
Linux and is easy to setup. The PI makes a very nice low end server
and because of the very low power consumption it is an excellent
choice for a 7/24/365 server.</p>

<p>
I set the PI OS up with weewx, postfix (email) and the apache (web server).
I also added a 500 Gigabyte USB external hard drive and use the PI
as my backup server as well.</p>

<p>Please visit my <a href="http://www.bartonphillips.com/weewx">
Weather Station.</a></p>
<p>New Battery: April 7, 2018; Previous: Jan. 1, 2018, Sept. 1, 2017; May 2017; August 22, 2016.</p>
<hr>
$footer
EOF;

<?php
require_once("/var/www/includes/siteautoload.class.php");

$S = new Blp;

$h->title = "About Weather Station";
$h->banner = "<h1>About My Weather Station</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>
I have had a Weather Station for the past 20 years. First at our
home in Canoga Park, CA and now at our home in Granby Ranch, CO.
My first weather station was an Origon Scientific which I puchased
in the early 1990's. When we moved to Granby Ranch, in the high
mountains, I bought a
<a href="http://www.davis.com/Category/Davis_Instruments_Vantage_Vue_Wireless_Weather_Station/59174?referred_id=20948&gclid=Cj0KEQiA6JemBRC5tYLRwYGcwosBEiQANA3IB0U22-9m8dRlzQCt5kVJp-ORsTKknZfDdOK204-IBmoaAjNt8P8HAQ">
Davis Instruments Vantage Vue Wireless Weather Station</a>. I changed
my station from the Origon Scientific to Davis because we now live
at 8,400 feet above sea level and the Origon was not rated for such
high altitude.</p>

<p>
The Vantage Vue has provided very satisfoatory results. I looked
around for weather station software to run on my Linux computers and
found <a href="http://www.weewx.com/">Weewx</a>. The software is
written in Python and the website has very good documentation and
support. As with most thing Linux the weather station software is
free and open source.</p>

<p>
I installed the Davis equipment on my back patio. The system is very
easy to setup and I had it up and running within a couple of hours.
The weewx software was very easy to install and configure and I had
it all working the same day I received the Davis equipment.</p>

<p>
I originally had the software running on my home computer which was
at the time a Dell 530, which I bought because at the time Dell was
offering systems with Linux installed. After 10 years the Dell died
and I wasn't able to fix it. I bought an HP Envy this year, which
was a nice upgrade. I didn't want to use this new system as a server
because I wanted to be able to turn it on and off and experment with
different operationg systems etc.</p>

<p>
I bought a <a href="http://www.raspberrypi.org/">Raspberry PI</a>
which is a small low power fanless system. It sells for around $40 and uses very
little electricity. The PI has an ARM cpu but runs a version of
Linux and is easy to setup. The PI makes a very nice low end server
and because of the very low power consumption it is an excelent
choice for a 7/24/365 server.</p>

<p>
I set the PI OS up with weewx, postfix (email) and nginx (web server).
I also added a 500 Gigabyte USB external hard drive and use the PI
as my backup server as well.</p>

<p>Please visit my <a href="http://www.bartonphillips.com/weewx">
Weather Station.</a></p>
<hr>
$footer
EOF;

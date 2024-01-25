<?php
// BLP 2022-09-09 - NOTE: All of the resources come from https://bartonphillips.net or
// https://bartonlp.com/otherpages/ (tracker.php, tracker.js, beacon.php). Therefore, we
// can load this from http://bartonphillips.org:8080 or from
// https://www.bartonphillips.com/fromrpi.php. This lets me load the rpi page with https.
// All fromrpi.php does is:
// $page = file_get_contents("http://bartonphillips.org:8080/index.eval");
// and I then do an eval(). See the code in fromrpi.php.
// The 'index.eval' is a symlink of 'index.php'. I have to do this because the PHP wrappers will evaluate
// the .php file.
// The native index.php gets its information from the local rpi's mysitemap.json.
// NOTE, while /var/www/vendor/bartonlp/site-class/includes has tracker.php, tracker.js and beacon.php they
// are not sourced from the rpi but rather from my server's https://bartonlp.com/otherpages which has symlinks
// to the server's vendor/bartonlp/site-class/includes.

// BLP 2023-09-08 - We check to see if $_site is set. The fromrpi.php program sets $_site. See that
// program for how that is done.

if(!$_site) {
  // BLP 2023-09-08 - if $_site is not set then this is not fromrpi.php but rather
  // 'http://bartonphillips.org:8000'. So we get the mysitemap.json from the RPI using the
  // siteload.php from the RPI.

  $_site = require_once(getenv("SITELOADNAME"));
}

$S = new SimpleSiteClass($_site);
//vardump("\$S", $S);
$phpVersion = PHP_VERSION;
$siteclassVersion = SiteClass::getVersion();

$S->msg = "PhpVersion: $phpVersion<br>"; // This is the local phpVersion on the rpi.
$S->msg1 = "SiteClass: $siteclassVersion<br>";
$S->title = "RPI PHP Page";

// This script and the css below are the only inline resouces.
// ximage.js uses glob.proxy.php at https://bartonphillips.net.
// The symlink is in https://bartonphillips.net to the location of the PasoRobles photos.
// Normally this is at bartonlp.org/www/PhotosFromHPenvy/. PasoRobles2013 is there.

$S->h_script =<<<EOF
  <script src="https://bartonphillips.net/js/ximage.js"></script>
  <script>dobanner("PhotosFromHPenvy/PasoRobles2013/*.JPG", "Trip to Paso Robles 2013", {recursive: 'no', size: '100', mode: "rand"});</script>
EOF;

$S->css = <<<EOF
#show {
   width: 20rem;
   margin: auto;
}
#show img {
  width: 100%;
}
h1 {
  line-height: 1.35rem;
}
span {
  font-size: 1.2rem;
}
EOF;

date_default_timezone_set('America/New_York');
$d = date("l F j, Y H:i T");

$S->banner = "<h1 class='center'>Barton's Raspberry Pi<br><span>$d</span></h1>";

$site = escapeltgt(print_r($_site, true));
$ss = escapeltgt(print_r($S, true));

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<!--ip=$ip<br>
agent=$agent-->
<section id="show"></section>
<section class='center'>
<a href="http://www.bartonphillips.com">My Home Page</a><br><br>
</section>
<!--<pre>$site</pre>
<pre>$ss</pre>-->
<hr>
$footer
EOF;

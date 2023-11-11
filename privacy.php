<?php
// BLP 2023-02-25 - use new approach
// Privacy Statement for bartonphillips.com

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$S->title = "Bartonphillips.com Privacy Statement";
$S->meta = "<meta name='Editor' content='Bonnie Burch'>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<h1>Privacy Statement</h1>
  <p>We do not collect <i>tracking COOKIES</i>, but we do collect anonymous information, like IP
    addresses, user agent strings, dates and times, fingerprints and geo-location data. While those are anonymous, they provide some
    information about your ISP and possibly your computer. You can always opt out of providing geo-location information.
    This site uses <b>PHP</b> and the server can collect anonymous information even if you have JavaScript disabled.</p>
  <p>If you have looked at our source code, you have probably seen <b>tracker.js</b> and <b>geo.js</b> loading
    <i>jquery</i>. Those programs collect anonymous information and adds it to our
    databases.</p>
  <p>If you really want to surf the web anonymously, go to your local library and use one
    of its computers. Don't take your cell phone and wear dark classes and a COVID mask. Also, don't park near the library.
    Do not use your email address or provide other personal information.
    This probably is not the way you want to enjoy the web, but doing it this way will give you a pretty
    anonymous footprint.</p>
  <p>As stated on our home page, we do not collect <i>tracking COOKIES</i>, but we do collect anonymous information.
    However, other pages you may link to, even on our domain, do collect and use JavaScript extensively.</p>

  <p>We will never sell your information (as anonymous as it is) to any third party.</p>

  <p>If you want to see what information we collect, visit
    <a target="_blank" href="https://bartonphillips.net/webstats.php?blp=8653&site=$S->siteName">Webstats</a>.</p>
  <p>Good luck and have fun.</p>
<hr>
$footer
EOF;

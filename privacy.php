<?php
// Privacy Statement for bartonphillips.com
$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");
$S = new $_site['className']($_site); // takes an array if you want to change defaults
$h->title = "Bartonphillips.com Privacy Statement";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>Privacy Statement</h1>
  <p>We do not collect tracking COOKIES and only collect information that is anonymous like IP
    address, user agent string, dates and times. While this is anonymous it does provide some
    information about your ISP and possibly your computer etc.</p>
  <p>If you really want to surf the web anonymously you should go to your local library and use one
    of their computers. Do not use your email address or any other information while surfing the
    web. This is probably not the way you want to enjoy the web but it will give you a pretty
    anonymous footprint.</p>
  <p>As stated on our home page the &quot;Home Page at http://www.bartonphillips.com&quot; does not
    collect tracking information but other pages you will link to, even on our domain, do collect
    and use JavaScript extensively.</p>

  <p>We will never sell your information (as anonymous as it is) to any third party. Some information is available on our
    website at <a href="http://www.bartonphillips.com/webstats.php">http://www.bartonphillips.com/webstats.php</a>.
    It is freely available to anyone who is interested. It is pretty
    esoteric, but it is pretty extensive.</p>

  <p>Good luck and have fun.</p>
$footer
EOF;

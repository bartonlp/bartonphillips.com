<?php
// Privacy Statement for bartonphillips.com
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);
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

  <p>We will never sell your information (as anonymous as it is) to any third party.</p>

  <p>Good luck and have fun.</p>
$footer
EOF;

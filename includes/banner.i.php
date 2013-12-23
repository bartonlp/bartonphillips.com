<?php
/*
We could have lots of additional stuff here like a greeting and a navbar etc.

    if($this->id) {
      $greet = "<h2>Welcome $this->fname $this->lname</h2>";
    } else {
      $greet = <<<EOF
<h1>Welcome</h1>
EOF;
    }
    if($nonav == false) {
      // Nav bar under banner
      $navbar = <<<EOF
<div id="navbar">
<ul>
<li><a href="/index.php">Welcome</a></li>
<li><a href="/resources.php">Resources</a></li>
<li><a href="/memberspage.php">Members&nbsp;Page</a></li>
<li><a href="/events.php">Calendar</a></li>
<li><a href="/news.php">News</a></li>
<li><a href="/bboard.php">Bulletin Board</a></li>
</ul>
</div>
EOF;
    }
*/

  // Include GoogleAnalytics

  include_once('analyticstracking.php'); // sets $GoogleAnalytics

  // WARNING About MSIE!

  preg_match('/(MSIE.*?);/', $this->agent, $m);
  $msie = $m[1];

  $pageBannerText = <<<EOF
$GoogleAnalytics
<!--[if lte IE 8]>
<hr>
<p style='color: red; background-color: white; border: 1px solid black; padding: 5px;'>
You are running a version of Internet Explorer ($msie).
Unfortunatly IE is not standards compliant.
There are several features that may not work correctly on this page depending on the
version of Internet Explorer you are using (actually almost everything is not going to work).
This page has been tested with
<a href='http://www.getfirefox.com'>Firefox</a>,
<a href='http://www.google.com/chrome'>Chrome</a>,
<a href='http://www.opera.com'>Opera</a>,
<a href='http://www.apple.com/safari/download/'>Safari</a>
and works well. You don't need to live with a sub-standard and outdated web browser.
For best results download either Firefox or Chrome. I highly recomend changing your
browser to one of the standard complient browsers. If you must use Internet Explorer then
upgrade to IE9 which is pretty
standard complient (but of course Microsoft is forcing you to also upgrade to Windows 7 or 8 also)!</p>
<![endif]-->
<!--[if gt IE 8]>
<div  style='text-align: center; width: 50%; margin: auto;
color: white; background-color: #BABAD4; border: 1px solid red; padding: 5px;'>
<p>
You are using Internet Explorer $msie. While this site has been tested with IE9 you are still
using Internet Explorer which has been non-standard for years. Send Microsoft a message,
upgrade to Firefox, Chrome or Opera Now, these browsers
really work.
With security, stability, speed and much more. All of these browsers are standard complient and
really work. They are FREE and easy to install! Don't live in the 1900's with Microsoft.</p>
<p>You can get a real browser from one of these links:</p>
<ul style='text-align: left'>
<li><a href='http://www.getfirefox.com'>Get Firefox</a></li>
<li><a href='http://www.google.com/chrome'>Get Chrome</a></li>
<li><a href='http://www.opera.com'>Get Opera</a></li>
</ul>
</div>
<![endif]-->
<!--[if lte IE 7]>
<div style='color: white; background-color: red; border: 1px solid black; padding: 5px;'>
<p>
Really any Internet Explorer less then version 9 just does not work! It doesn't take much to upgrade to
a real browsers that supports the Web Standards and they are all FREE. Upgrade to Firefox, Chrome, or
Opera. You don't need to live in the 1900's just because Microsoft wants your money.</p>
<ul style='text-align: left'>
<p>You can get a real browser from one of these links:</p>
<li><a href='http://www.getfirefox.com'>Get Firefox</a></li>
<li><a href='http://www.google.com/chrome'>Get Chrome</a></li>
<li><a href='http://www.opera.com'>Get Opera</a></li>
</ul>
</div>
<![endif]-->

EOF;

$pageBannerText .= <<<EOF
<header>
<div>
   <img id='blpimg' src="/blp-image.png" alt="Barton's Picture"/>
   <a href="http://linuxcounter.net/">
   <img id='linuxcounter' src="/images/146624.png" alt="linux counter image."/>
   </a>
</div>

$mainTitle
</header>
EOF;
?>
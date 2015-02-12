<?php
require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp; // count page
$h->title = "About This Site";
$h->css = <<<EOF
  <style>
main {
  text-align: center;
  margin: 30px auto 0 auto;
  width: 95%;
  border: 1px solid black
}
  </style>
EOF;

$pageHead = $S->getPageHead($h);
$footer = $S->getFooter();
echo <<<EOF
$pageHead
<main>
<h2>About This Web Site and Server</h2>

<div id="aboutWesssbSite">

<div id="runWith">
  <p>Designed by Barton L. Phillips<br/>
     Copyright &copy; 2010 <a
     href="mailto:bartonphillips@gmail.com">Barton L. Phillips</a></p>
  
	<p>This site is hosted by <a href="http://digitalocean.com">
		 <img alt="Digital Ocean" align="middle"
					src="http://bartonlp.com/html/images/digitalocean.jpg")">
		 </a>
		 </p>

  <p>This site is run with Linux, Apache, MySql, and PHP.</p>
	<p><img src="http://bartonlp.com/html/images/linux-powered.gif" alt="Linux Powered"></p>
	<p><a href="http://www.apache.org/"><img border="0" src="http://bartonlp.com/html/images/apache_logo.gif" alt="Apache" width="400" height="148"></a></p>
	<p><a href="http://www.mysql.com"><img border=0 src="http://bartonlp.com/html/images/powered_by_mysql.gif" alt="Powered by MySql"></a></p>
	<p><a href="http://www.php.net"><img src="http://bartonlp.com/html/images/php-small-white.png" alt="PHP Powered"></a></p>
  <p><a href="http://jquery.com/"><img
  src="http://bartonlp.com/html/images/jquery.gif" alt="jQuery logo"
  style="background-color: black"/></a></p>

  <p><a href="http://www.mozilla.org"><img src="http://bartonlp.com/html/images/bestviewedwithmozillabig.gif" alt="Best viewed with Mozilla or any other browser"></a></p>
	<p><a href="http://www.mozilla.org"><img
	src="http://bartonlp.com/html/images/shirt3-small.gif" alt="Mozilla"></a></p>
	<p><img src="http://bartonlp.com/html/images/msfree.png" alt="100% Microsoft Free"></p>

	<p><a
  href="http://www.netcraft.com/whats?url=http://www.bartonphillips.com">
	<img src="http://bartonlp.com/html/images/powered.gif" width=90 height=53 border=0 alt="Powered By ...?"></a>
	</p>
</div>
</div>
</main>
$footer
EOF;

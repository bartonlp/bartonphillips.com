<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp; // count page
$h->title = "About This Site";

$pageHead = $S->getPageHead($h);
$footer = $S->getFooter();
echo <<<EOF
$pageHead
<div
 style='text-align: center; margin: 30px auto 0 auto; width: 60%; border:
    1px solid black'>
<h2>About This Web Site and Server</h2>

<div id="aboutWesssbSite">

<div id="runWith">
  <p>Designed by Barton L. Phillips<br/>
     Copyright &copy; 2010 <a
     href="mailto:bartonphillips@gmail.com">Barton L. Phillips</a></p>
  
	<p>This site is hosted by <a href="http://inmotionhosting.com">
		 <img width="200" height="40" border="0" align="middle"
						alt="Inmotion Hosting"
						src="http://www.inmotionhosting.com/img/logo-imh.svg">
		 </a>
		 </p>

  <p>This site is run with Linux, Apache, MySql, and PHP.</p>
	<p><img src="images/linux-powered.gif" alt="Linux Powered"></p>
	<p><a href="http://www.apache.org/"><img border="0" src="images/apache_logo.gif" alt="Apache" width="400" height="148"></a></p>
	<p><a href="http://www.mysql.com"><img border=0 src="images/powered_by_mysql.gif" alt="Powered by MySql"></a></p>
	<p><a href="http://www.php.net"><img src="images/php-small-white.png" alt="PHP Powered"></a></p>
  <p><a href="http://jquery.com/"><img
  src="http://www.granbyrotary.org/images/logo_jquery_215x53.gif" alt="jQuery logo"
  style="background-color: black"/></a></p>

  <p><a href="http://www.mozilla.org"><img src="images/bestviewedwithmozillabig.gif" alt="Best viewed with Mozilla or any other browser"></a></p>
	<p><a href="http://www.mozilla.org"><img
	src="/images/shirt3-small.gif" alt="Mozilla"></a></p>
	<p><img src="images/msfree.png" alt="100% Microsoft Free"></p>

	<p><a
  href="http://www.netcraft.com/whats?host=www.bartonphillips.com">
	<img src="images/powered.gif" width=90 height=53 border=0 alt="Powered By ...?"></a>
	</p>
</div>
</div>

</div>
$footer
EOF;
?>

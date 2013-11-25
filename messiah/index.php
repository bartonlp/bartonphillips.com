<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Messiah;

$h->title = "Mountain Messiah";
$h->banner = <<<EOF
<header id="header">
<img src="MountainMessiah.png" alt="logo"/><br>
<img src="George_Frideric_Handel.jpg" alt="Picture of Handel" title="George Frideric Handel" />
EOF;

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

echo <<<EOF
$top
<h1>Sixteenth Annual Mountain Messiah</h1>
<p>At 8,850 feet, this is the highest sung rendition of the Messiah
by George Frederick Handel</p>
</header>
<hr>
<article class="content">
<section>
<h2>Information coming soon for 2013 Messiah.</h2>
<!--
<h2>Program</h2>
</section>

<section>
<h2>Afterglow</h2>
<a href='mailto:barton@bartonphillips.com?subject=Messiah-Signup&body=Please+supply+Name+and+Email+address'>Sign Up</a></p>
-->
</section>
</article>
$footer
EOF;
?>
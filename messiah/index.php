<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Messiah;

$h->title = "Mountain Messiah 2013";
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
<p><i>Come lend your voice in song</i><br>
<i>or</i><br>
<i>Come and listen along</i></p>

<p>Sunday, December 22<br>
6:30 PM Pre-event entertainment<br>
7:00 PM Sing-a-long concert</p>
<p>Snow Mountain Ranch YMCA of the Rockies Chapel<br>
Located next to the administration building.</p>
<p>Messiah scores available at the concert.</p>
</section>
<hr>
<section>
<h2>Afterglow</h2>
<p>Holiday goodies, music, and merriment.<br>
Have cookies? Bring  some to share.</p>
</section>
</article>
$footer

EOF;
?>
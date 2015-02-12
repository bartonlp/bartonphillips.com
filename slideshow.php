<?php
require_once("/var/www/includes/siteautoload.class.php");
$S = new Tom;
$h = array('title'=>'Wrong Page', 'banner'=>'<h1>Go to the Home Page and follow the link</h1>');

list($top, $footer) = $S->getPageTopBottom($h);
echo <<<EOF
$top
<p>Follow the link on the <a href="/">Home Page</a></p>
$footer
EOF;

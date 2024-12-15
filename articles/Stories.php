<?php
$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

$S->title = "My Stories";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<ul>
<li><a href="GreatBooks.pdf">Great Books</a> (short story)
<li><a href="Sandie.pdf">One Summer</a> (short story)
<li><a href="MyStory-Edited.pdf">Down the Hill and More</a> (short story)
</ul>
<hr>
$footer
EOF;

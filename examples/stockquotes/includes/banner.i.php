<?php
return <<<EOF
<header>
  <a href="http://www.bartonphillips.com">
    <img id='logo' src="https://bartonphillips.net/images/blp-image.png"></a>
  <!-- the 'a' tag must be at the end of the image src otherwise we get an '-'-->
  <a href="http://linuxcounter.net/">
    <img id='linuxcounter' src="https://bartonphillips.net/images/146624.png">
  </a>
$mainTitle
</header>
EOF;

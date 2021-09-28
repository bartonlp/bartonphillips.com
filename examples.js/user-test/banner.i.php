<?php

return <<<EOF
<header>
  <a href="http://www.bartonlp.com">
    <img id='logo' src="https://bartonphillips.net/images/blp-image.png" alt="barton"></a>
  <!-- the 'a' tag must be at the end of the image src otherwise we get an '-'-->
  <a href="http://linuxcounter.net/">
    <img id='linuxcounter' src="https://bartonphillips.net/images/146624.png" alt="linux counter image.">
  </a>
$mainTitle
<noscript>
<p style='color: red; background-color: #FFE4E1; padding: 10px'>
<img src="/tracker.php?page=noscript&id=$this->LAST_ID" alt="noscript">
Your browser either does not support <b>JavaScripts</b> or you have JavaScripts disabled, in either case your browsing
experience will be significantly impaired. If your browser supports JavaScripts but you have it disabled consider enabaling
JavaScripts conditionally if your browser supports that. Sorry for the inconvienence.</p>
</noscript>
</header>
EOF;

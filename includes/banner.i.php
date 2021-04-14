<?php
// BLP 2021-03-26 -- add nodb logic

if($this->nodb !== true && $this->noTrack !== true) {
  $image2 =<<<EOF
  <a>
    <img id='linuxcounter' src="/tracker.php?page=normal&id=$this->LAST_ID" alt="linux counter image.">
  </a>
EOF;
  $image3 = "<img src='tracker.php?page=noscript&id=$this->LAST_ID'>";
}

return <<<EOF
<header>
  <a href="http://www.bartonphillips.com">
    <img id='logo' src="https://bartonphillips.net/images/blp-image.png"></a>
$image2
$mainTitle
<noscript>
<p style='color: red; background-color: #FFE4E1; padding: 10px'>
$image3
Your browser either does not support <b>JavaScripts</b> or you have JavaScripts disabled, in either case your browsing
experience will be significantly impaired. If your browser supports JavaScripts but you have it disabled consider enabaling
JavaScripts conditionally if your browser supports that. Sorry for the inconvienence.</p>
</noscript>
</header>
EOF;

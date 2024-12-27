<?php
// BLP 2024-12-16 - Added <style> in <noscript>.
// See footer.i.php for the ending </div>

if(!class_exists('Database')) header("location: https://bartonlp.com/otherpages/NotAuthorized.php");

return <<<EOF
<header>
  <!-- bartonphillips.com/includes/banner.i.php -->
  <a href="$h->logoAnchor">$image1</a>
  $image2
  $mainTitle
  <noscript>
    <p style='color: red; background-color: #FFE4E1; padding: 10px'>
      $image3
      Your browser either does not support <b>JavaScripts</b> or you have JavaScripts disabled, in either case your browsing
      experience will be significantly impaired. If your browser supports JavaScripts but you have it disabled consider enabaling
      JavaScripts conditionally if your browser supports that. Sorry for the inconvienence.</p>
    <p>The rest of this page will not be displayed.</p>
    <style>#content { display: none; }</style>
  </noscript>
</header>
<div id="content"> <!-- BLP 2024-12-16 - See footer.i.php for ending </div>. -->
EOF;

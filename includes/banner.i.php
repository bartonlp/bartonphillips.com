<?php
// BLP 2025-04-08 - New using $b->names

if(!class_exists('dbPdo')) header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=bartonphillips.com&page=banner.i.php");

return <<<EOF
<header>
  <!-- bartonphillips.com/includes/banner.i.php -->
  <a href="$b->logoAnchor">$b->image1</a>
  $b->image2
  $b->mainTitle
  <noscript>
    <p style='color: red; background-color: #FFE4E1; padding: 10px'>
      $b->image3
      Your browser either does not support <b>JavaScripts</b> or you have JavaScripts disabled, in either case your browsing
      experience will be significantly impaired. If your browser supports JavaScripts but you have it disabled consider enabaling
      JavaScripts conditionally if your browser supports that. Sorry for the inconvienence.</p>
    <p>The rest of this page will not be displayed.</p>
    <style>#content { display: none; }</style>
  </noscript>
</header>
<div id="content"> <!-- See footer.i.php for ending </div>. -->
EOF;

<?php
// bottom area

if(!class_exists('dbPdo')) header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=bartonphillips.com&page=footer.i.php");

return <<<EOF
</div> <!-- Ending for <div id="content". See banner.i.php -->
<footer>
  <!-- bartonphillips.com/includes/footer.i.php -->
  $f->aboutwebsite
  <div id="address">
    <address>
      $f->copyright
      $f->address
      $f->emailAddress
    </address>
  </div>
  $f->msg
  $f->msg1
  $f->counterWigget
  $f->lastmod
  $f->msg2
</footer>
$f->geo
$f->extra
$f->script
$f->inlineScript
</body>
</html>
EOF;

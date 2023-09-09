<?php
// Footer file

// BLP 2023-09-07 - added to let me know if someone calls this directly.
 
if(!class_exists("SiteClass")) {
  $ip = $_SERVER['REMOTE_ADDR'];
  error_log("examples.js/user-test/footer.i.php: Called directly: $ip");
  echo "<h1>Not Authorized</h1><p>This file is not to be run directly, rather it is used by another file</p>";  
  exit();
}

return <<<EOF
<footer>
<!-- user-test footer.i.php -->
$b->aboutwebsite
<div id="address">
<address>
  $b->copyright
  $b->address
  $b->emailAddress
</address>
</div>
{$b->msg}
{$b->msg1}
<!-- we are running footer with noTrack = true; -->
<!-- $counterWigget -->
{$b->msg2}
<p>$lastmod</p>
</footer>
$geo
$b->script
$b->inlineScript
</body>
</html>
EOF;

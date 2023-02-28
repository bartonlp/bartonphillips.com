<?php
// Footer file

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

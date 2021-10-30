<?php
// Footer file

return <<<EOF
<footer>
<h2><a target="_blank" href='../../aboutwebsite.php'>About This Site</a></h2>
<div id="address">
<address>
  Copyright &copy; $this->copyright
</address>
<address>
Barton Phillips<br>
$this->address<br>
<a href='mailto:bartonphillips@gmail.com'>
  bartonphillips@gmail.com
</a>
</address>
</div>
{$b->msg}
{$b->msg1}
<!-- we are running footer with noTrack = true; -->
<!-- $counterWigget -->
{$b->msg2}
<p>Last Modified: $lastmod</p>
</footer>
</body>
</html>
EOF;

<?php

$lastmod = date("M j, Y H:i", getlastmod());

return <<<EOF
<footer>
<address>
  Copyright &copy; $this->copyright
</address>
<br>
{$arg['msg']}
{$arg['msg1']}
$counterWigget
<div style="text-align: center;">
<p id='lastmodified'>Last Modified:&nbsp;$lastmod</p>
{$arg['msg2']}
</div>
</footer>
</body>
</html>
EOF;

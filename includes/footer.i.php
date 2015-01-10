<?php
   // Footer file
$statcounter = <<<EOF
<!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=10131375; 
var sc_invisible=1; 
var sc_security="5d14a98f"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="free hit
counters" href="http://statcounter.com/free-hit-counter/"
target="_blank"><img class="statcounter"
src="http://c.statcounter.com/10131375/0/5d14a98f/1/"
alt="free hit counters"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->
EOF;

if(isset($arg['statcounter'])) {
  if(is_string($arg['statcounter'])) {
    $statcounter = $arg['statcounter'];
  } elseif($arg['statcounter'] === false) {
    $statcounter = '';
  }
}

$pageFooterText = <<<EOF
<footer>
<h2><a target="_blank" href='aboutwebsite.php'>About This
   Site</a></h2>

<div id="address">
<address>
  Copyright &copy; 2015 Barton L. Phillips</address>
<address>
Barton Phillips, PO Box 4152, CO 80446-4152</address>
<address>
<a
 href='mailto:bartonphillips@gmail.com?to=test@bartonlp.com&subject=test'>bartonphillips@gmail.com
</a>
</address>
</div>

</div>

${arg['msg']}
$counterWigget
</footer>
$statcounter
</body>
</html>
EOF;

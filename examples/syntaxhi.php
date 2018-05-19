<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->script = <<<EOF
<script src="https://bartonphillips.net/js/jquery-syntaxhighlighter.js"></script> 
<script>
$(window).on("load", function() {
  $.SyntaxHighlighter.init();
  $("body link:last-child").after('<style>prettyprint, pre.prettyprint {border: none;}</style>');
});
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<h1>TEST</h1>
<pre class="highlight">
let x = 1.4;
let y = 3.0;
let ret = x * y;
console.log(ret);
</pre>
$footer
EOF;

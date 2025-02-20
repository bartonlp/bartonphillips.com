<?php
$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$S->b_inlineScript =<<<'EOF'
$("#but").on("click", function(e) {
  const one = 1;
  const str = "STRING";

  const output = `<pre>
${one}
${str}
This is a string</pre>`;

  $("#div").html(output);
});
EOF;

$S->banner = "<h1>Test</h1>";
[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<button id="but">Something New</button>
<div id="div"></div>
<hr>
$bottom
EOF;

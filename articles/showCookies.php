<?php
// BLP 2023-02-25 - use new approach
// Compare PHP to JavaScript Cookies.
// Cookies with option 'httponly' set to true will not show in the JavaScript section.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->banner = "<h1>PHP vs JavaScript Cookies</h1>";

[$top, $footer] = $S->getPageTopBottom();

// Get the PHP cookies

$cookies = $_COOKIE;

$x = "PHP Cookies:<br>";
foreach($cookies as $k=>$v) {
  $x .= "$k: $v<br>";
}

echo <<<EOF
$top
<hr>
<div id="content"></div>
<hr>
<script>
  // Now get the JavaScriptCookies
  
  let x = document.cookie.split('; '); // Seperate the cookies
  let z = ''; // Start empty rather than 'undefined'
  
  for(let y=0; y < x.length; ++y) {
    z += x[y] + "<br>";
  }
  z = "<p>$x</p><p>JavaScript Cookies:<br>" + z + "</p>";

  // Put results in 'content'
  
  $("#content").html(z);
</script>
$footer
EOF;


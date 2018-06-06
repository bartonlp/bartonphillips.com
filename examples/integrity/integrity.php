<?php
$_site = require_once(getenv("SITELOADNAME"));
$_site->headFile = null;
$S = new $_site->className($_site);

// To generate the hash run:
//   shasum -b -a 256 phpdate.js | xxd -r -p | base64
// Then copy the hash. To use it make a string like "sha256-$hashfor_xxx"
// You can use 256 or 384 or 512 just change the "shaNNN-" to the value you use.

$hashfor_blp = "ZWN6m6Iwn5mMss8ihvf2cZJmVhLcx2fEmSXeh6IV8h0="; // sha256
$hashfor_phpdate = "25u4z3OPYNQswt8jkHKp64iAS7FXhxxr2FiAR7cfEz14zUMaEadrjS9fl8DOtG7X"; // sha384

$h->link =<<<EOF
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css"
   integrity="sha256-$hashfor_blp" crossorigin=""/>
  <link rel="stylesheet" href="test.css" integrity=""/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine|Jacques+Francois+Shadow|Lora|Rancho&effect=shadow-multiple">
EOF;

$h->css =<<<EOF
  <style>
.script {
  font-family: 'Tangerine';
  font-size: 1.5rem;
}
  </style>
EOF;

$b->script =<<<EOF
  <script src="https://bartonphillips.net/js/phpdate.js" integrity="sha384-$hashfor_phpdate" crossorigin="anonymous"></script>
  <script>
console.log("This is via an eval(2+2):", eval("2+2"));
let thisdate = date("Y-m-d");
console.log("This is from phpdate:", thisdate);
const div = document.querySelector("#date");
div.innerHTML = thisdate;
  </script>
EOF;

//header("Content-Security-Policy: require-sri-for script style");

list($top, $footer) = $S->getPageTopBottom($h,$b);

echo <<<EOF
$top
<h1 class="center">This is a test</h1>
<div class="center">
<img src="https://www.bartonlp.com/heidi/uploads/heidi.jpg"/>
</div>
<div id="date" class="center"></div>
<p class="center script">This is an example of the <b>Tangerine</b> font.</p>
<p>The <b>.htaccess</b> file has:</p>
<ul>
<pre>
Header set Content-Security-Policy "default-src 'none'; \
script-src 'self' bartonphillips.net 'unsafe-inline' 'unsafe-eval'; \
style-src 'self' bartonphillips.net https://fonts.googleapis.com 'unsafe-inline'; \
img-src *; \
font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com bartonphillips.net;"
</pre>
</ul>
<p>This sets the <b>Content-Security-Policy</b> for this directory.</p>
$footer
EOF;

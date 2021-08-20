<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
//$_site->noTrack = true;
$S = new $_site->className($_site);

$h->banner = "<h1>How to Use CSS Variables</h1>";
$h->css =<<<EOF
<style>
body {
  padding: 1rem;
  font-family: sans-serif;
}
article {
  padding: 2rem;
  margin-bottom: 1rem;
  cursor: pointer;
  background: lightgray;
  transition: 100ms;
}
article h2:hover {
  background: var(--custome_color);
  color: var(--color);
}
h2 {
  font-size: 1.8rem;
  margin-bottom: .5rem;
}
.my-element {
  position: relative;
  background: pink;
  width: 500px;
  height: 100px;
}
.my-element::after {
  position: absolute;
  content: var(--testit, none);
  padding-left: 100px;
  margin-left: 5px;
  top: 1em;
  left: 0px;
  background: lightblue;
}
</style>
EOF;

$b->script =<<<EOF
<script>
  $(".my-element").on("click", function() {
    $(this).css('--testit', '"hello world"');
  });
</script>
EOF;

$h->script =<<<EOF
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">
EOF;

[$top, $footer] = $S->getPageTopBottom($h,$b);

$my_element1 =<<<EOF
<style>
.my-element {
  position: relative;
  background: pink;
  width: 500px;
  height: 100px;
}
.my-element::after {
  position: absolute;
  content: var(--testit, none);
  padding-left: 100px;
  margin-left: 5px;
  top: 1em;
  left: 0px;
  background: lightblue;
}
</style>
EOF;
$my_element1 = escapeltgt($my_element1);

$my_element2 =<<<EOF
<script>
  $(".my-element").on("click", function() {
    $(this).css('--testit', '"hello world"');
  });
</script>
EOF;
$my_element2 = escapeltgt($my_element2);

$other =<<<EOF
<style>
article {
  padding: 2rem;
  margin-bottom: 1rem;
  cursor: pointer;
  background: lightgray;
  transition: 100ms;
}
article h2:hover {
  background: var(--custome_color);
  color: var(--color);
}
article h2 {
  font-size: 1.8rem;
  margin-bottom: .5rem;
}
</style>

<article>
<h2 style='--custome_color: red;'>This is a test</h2>
<h2 style='--custome_color: blue; --color: white;'>More test</h2>
<div class='my-element'>This is without</div>
</article>
EOF;
$other = escapeltgt($other);

echo <<<EOF
$top
<p>
This has a 'div' with a class of 'my-element', and 'my-element' has a '::after'  with 'content' of 'none'.
Now I want to change the 'content' to 'hello world'. To do that I use a css vaiable;</p>
<pre class="brush: css">
$my_element1
</pre>
<p>Then with a little jQuery like this:</p>
<pre class="brush: js">
$my_element2
</pre>
<p>Now when you click the line that says 'This is without' you will see the 'hello world' in light blue.</p>
<p>The two lines above 'This is without' are also done with a CSS variable (NO javascript) to get the hover effect.</p>
<pre class="brush: html">
$other
</pre>
<article>
<h2 style='--custome_color: red;'>This is a test</h2>
<h2 style='--custome_color: blue; --color: white;'>More test</h2>
<div class='my-element'>This is without</div>
</article>
$footer
EOF;

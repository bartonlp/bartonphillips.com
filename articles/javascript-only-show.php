<?php
// JavaScript Only Show
// This is only used by javascript-only-nojquery.html to show the source code.

// Get the original php file
if($_GET['item'] == 1) {
  $file = file_get_contents("javascript-only-nojquery.html");
} elseif($_GET['item'] == 2) {
  $file = file_get_contents("javascript-only.js");
} else {
  echo "Error";
  exit();
}
// Clean up left and right arrows, $ \n and quotes. Make them all displayable.
$file = preg_replace(array("/</","/>/", "/\\$/", "/\n/", '/"/'), array("&lt;","&gt;", "\$", "<br>", "'"), $file);

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<style>
pre {
  font-size: 1.5em;
  overflow: auto;
  padding: .5em;
  border-left: .5em solid gray;
  background-color: #E5E5E5;
}
</style>
</head>
<body>
<pre>$file</pre>
</body>
</html>
EOF;

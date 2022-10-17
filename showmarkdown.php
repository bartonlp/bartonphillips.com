<?php
// Show the markdown file
  
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new $_site->className($_site);

$h->css =<<<EOF
input, select { font-size: 1rem; padding-left: .3rem; margin-bottom: 1rem;}
input[type='text'] { width: 20rem; }
EOF;

[$top, $footer] = $S->getPageTopBottom($h);

if($_POST || ($get = $_GET['filename'])) {
  if($get) {
    $file = $get;
    $type = "GitHub";
  } else {
    $file = $_POST['filename'];
    $type = $_POST['type'];
  }

  switch($type) {
    case "GitHub":
      $parser = new \cebe\markdown\GithubMarkdown();
      $github =<<<EOF
  <link rel="stylesheet" href="https://bartonphillips.net/css/theme.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.js"></script>
  <script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
  <script>
jQuery(document).ready(function($) {
  var code = $("code[class|='language'");
  $(code).each(function(i, e) {
    var cl = $(e).attr('class');
    $(e).parent().addClass(cl.replace(/language-(.*)/, 'brush: $1'));
    $(e).parent().html($(e).html());
    $(e).remove();
  });
});
  </script>
EOF;
      break;
    case "Traditional":
      $parser = new \cebe\markdown\Markdown();
      break;
    case "Extended":
      $parser = new \cebe\markdown\MarkdownExtra();
      break;
    case "RAW":
      $parser = "RAW";
      break;
    default:
      echo "ERROR $type<br>";
      exit();
  }

  //echo "file: $file<br>";
  
  $output = file_get_contents($file);

  if(empty($output)) {
    echo "<h1 style='text-align: center; font-size: 2rem;'>File Not Found<br>$file</h1>";
    exit();
  }

  if($parser != "RAW") {
    $output = $parser->parse($output);
  }
} else {
  echo <<<EOF
$top
<form action='showmarkdown.php' method='post'>
  <input type='text' name='filename' autofocus placeholder='Enter Markdown File Name' required>
  <br>
  <select name='type'>
    <option>GitHub</option>
    <option>Traditional</option>
    <option>Extended</option>
    <option>RAW</option>
  </select>
  <br>
  <input type='submit' value='Submit'>
</form>
$footer
EOF;
}

echo $output;


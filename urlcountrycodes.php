<?php
// Show the ip and counter tables
$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$self = $S->self;

$h->css = <<<EOF
  <style>
input {
  font-size: 1rem;
  border-radius: .5rem;
  padding: .2rem;
  width: 5rem;
}
  </style>
EOF;

$h->script = <<<EOF
  <script>
jQuery(document).ready(function($) {
  $("input[type='submit']").click(function() {
    var code = $("input[type='text']").val();
    if(!code) {
      $("#results").html("<h2>NO CODE SUPPLIED</h2>");
      return false;
    }

    $("input[type='text']").val('');
    $.ajax({
      url: '/urlcountrycodes.php',
      data: {code: code},
      type: 'post',
      success: function(data) {
        console.log(data);
        $("#results").html(data);
      },
      error: function(err) {
        console.log(err);
      }
    });
    return false;
  });
});
  </script>
EOF;

$h->banner = "<h1>Country from URL sufix</h1>";
list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

if($code = $_POST['code']) {
  $n = $S->query("select description from urlcountrycodes where code='$code'");
  if($n) {
    $row = $S->fetchrow();
    $desc = $row[0];
    $other = "";
    if(preg_match("/(.*?)\s*(\(.*?\))$/", $desc, $m)) {
      $desc = $m[1];
      $other = "<p>$m[2]</p>";
    }
      
    echo <<<EOF
<h2>Description for Code '$code':<br/>
$desc</h2>
$other
EOF;
  } else {
    echo <<<EOF
<h2>Code '$code' not found</h2>
EOF;
  }
  exit();
}

// Render Page

echo <<<EOF
$top
<form action="$self" method="post">
Enter the Country Code: <input type="text" name="code" autofocus><br/>
<input type="submit" value="Submit"/>
</form>
<div id='results'></div>
$footer
EOF;

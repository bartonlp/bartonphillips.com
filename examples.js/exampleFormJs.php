<?php
// BLP 2021-10-31 -- This files uses exampleAjax.php for AJAX.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->h_script = <<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
jQuery(document).ready(function($) {
  $('#button').click(function() {
    let json = { test1: "test one", test2: "test two" };
  
    $.ajax({
      url: 'exampleAjax.php',
      data: {page: 'beacon', test: 'This is beacon speaking!', json: json},
      //dataType: "json",
      //headers: {'Content-Type': 'application/json'},
      type: 'post',
      success: function(data) {
        $('#info').html(data);
      },
      error: function(err) {
        console.log("error: ", err);
        $('#info').html(err.responseText);
      }
    });
    return false;
  });
});
</script>
EOF;
$S->css = <<<EOF
#form {
  border: 1px solid black;
  padding: 5px;
}
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<h1>Example useing a 'form' and a 'button'</h1>
<p>This file uses a 'form' and a JS 'button'.</p>

<p>The form below will do a regular &lt;form action="exampleAjax.php" method="post"&gt;</p>
<form action="exampleAjax.php" method="post">
<h2>Form</h2>
Text to forward: <input type="text" name="test" data-form-type='other'><br>
siteName: <input type="text" name="siteName" data-form-type='other'><br>
<input type="hidden" name="json" value='{"test1":"From Form1","test2":"From Form1"}'>
<input id='submit' type="submit" name="submit" value="Submit">
</form>
</div>

<p>This button uses javascript to do a post</p>
<button id='button'>Click Me</button><br>
<div id="info"></div>
$footer
EOF;

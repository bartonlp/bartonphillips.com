<?php
// BLP 2021-10-31 -- This files uses exampleAjax.php for AJAX.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$h->script = <<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
jQuery(document).ready(function($) {
  $('#button').click(function() {
    let json = { test1: "test one", test2: "test two" };
  
    $.ajax({
      url: 'exampleAjax.php',
      data: {page: 'beacon', test: 'This is beacon speaking!', json: json},
      type: 'post',
      success: function(data) {
        $('#info').html(data);
      },
      error: function(err) {
        $('#info').html(err);
      }
    });
    return false;
  });
});
</script>
EOF;
$h->css = <<<EOF
<style>
#form {
  border: 1px solid black;
  padding: 5px;
}
</style>
EOF;

[$top, $footer] = $S->getPageTopBottom($h);
echo <<<EOF
$top
<h1>Example useing a 'form' and a 'button'</h1>
<p>This file uses a 'form' and a JS 'button'.</p>

<div id="form">
<p>The form below will do a regular <form method="post" ...></p>
<form method="post" action="exampleAjax.php">
<h2>Form</h2>
Text to forward: <input type="text" name="test"><br>
siteName: <input type="text" name="siteName"><br>
<input id='submit' type="submit">
</form>
</div>

<p>This button uses javascript to do a post</p>
<button id='button'>Click Me</button><br>
<div id="info"></div>
$footer
EOF;

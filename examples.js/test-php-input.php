<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

echo <<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
jQuery(document).ready(function($) {
  $('#button').click(function() {
    let json = { test1: "test one", test2: "test two" };
  
    $.ajax({
      url: 'example1.php',
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
<form method="post" action="example1.php">
Test: <input type="text" name="test"><br>
siteName: <input type="text" name="siteName"><br>
<input id='submit' type="submit">
</form>
<button id='button'>Click Me</button><br>
<div id="info"></div>
EOF;

<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

session_start();

$div = $_SESSION['stuff'];

$S->b_inlineScript = <<<EOF
const response = document.getElementById('response');

window.addEventListener('message', (event) => {
  let lines = '';

  if(event.data) {
    data = JSON.parse(event.data);
    console.log("data: ", data);
    for(let i=0; i < data.length; ++i) {
      lines += "<input type='checkbox' value='" + data[i] + "'>" + data[i] + "<br>";
    }
    console.log("lines: " . lines);

    $("#response").html(lines);
  }
});

if(window.opener) {
  $(".popup").show();
  $("#standalone").hide();
} else {
  $("#standalone").show();
  $(".popup").hide();
}

$("button").on("click", function() {
  window.opener.postMessage({msg: "I got it OK"}, '*');
  window.close();
});

$("body").on("click", "input", function() {
  let value = $(this).val();
  window.opener.postMessage({msg: value}, '*');
  window.close();
});

window.opener.postMessage({ok: "I am up"}, '*');

EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<div id="contents">
<p id="response">This is just a place holder</p>
</div>
$footer
EOF;

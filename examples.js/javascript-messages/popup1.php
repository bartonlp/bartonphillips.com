<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->title = "PopUp Main";
$S->banner = "<h1>$S->title</h1>";

$mprog = escapeltgt(file_get_contents("popup1.php"));
$popprog = escapeltgt(file_get_contents("popup.php"));

$programs =<<<EOF
<b>Main Program</b><br>
<pre class="brush: php">$mprog</pre>
<b>Popup Program</b><br>
<pre class="brush: php">$popprog</pre>
EOF;

$items = glob("*.php");
$items = json_encode(array_merge($items, glob("*.html")));

$S->h_script =<<<EOF
<script src='https://bartonphillips.net/js/syntaxhighlighter.js'></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">
EOF;

$S->b_inlineScript = <<<EOF
const items = $items;
const response = document.getElementById('response');
let run = '';
window.addEventListener('message', (event) => {
  if(event.data?.msg) {
    run = event.data.msg;
    $("#response").html(`You have selected: <b>\${event.data.msg}</b>`);
    $("#run").show();
  } else if(event.data?.ok) {
    console.log("Got ok message: ", event.data.ok);
    sendMessage();
  }
})

let newWindow;

const openNewWindow = () => {
  const params = `width=300,height=900`;
  newWindow = window.open('popup.php', '_blank', params);
};

const sendMessage = () => {
  console.log("Send: ", items);
  newWindow.postMessage(JSON.stringify(items), '*');
};

$("#getitems").on("click", function() {
  openNewWindow();
});

$("#programdata").hide();
$("#run").hide();

$("#showprogram").on("click", function() {
  $("#programdata").show();
  $(this).hide();
});

$("body").on("click", "#run", function() {
  document.location = run;
});
EOF;

$S->css = "#responsearea { border: 1px solid black; width: 500px; }";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>This program does a PHP <i>glob("*")</i> to get a list of programs in this directory.
Then Javascript takes over and when the button below is pressed it opens a <i>popup</i> window that lists all of the
items in the directory and lets you select one.</p>
<button id="getitems">Get Items</button>

<div id="responsearea">
<p id="response"><i>Respon area</i></p>
<button id="run">Run Returned Item</button>
</div>

<hr>
<button id="showprogram">Show Sources</button>

<div id="programdata">
$programs
</div>
<hr>
$footer
EOF;

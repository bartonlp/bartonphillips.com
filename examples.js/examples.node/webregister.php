<?php
// Register with the websocket server (server3.php) running under node.

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$b->script = <<<EOF
<script>
'use strict';
  
let firstTime = true;
let audio = new Audio('Short-notification-sound.mp3');
let chatUser, chatText, siteId;
let webSocket;

if(!('WebSocket' in window)) {
  $("#info").html("<p>Fatal Error No WebSocket</p>");
} else {
  connect();
}
  
function connect() {
  try {
    const host = 'wss://bartonlp.org:8089/';

    webSocket = new WebSocket(host);

    webSocket.onopen = function() {
      $('#info').append('<p>Started</p>');
      if(webSocket && webSocket.readyState == 1) {
        try {
          console.log("sending: reg");
          webSocket.send("reg");
        } catch(exception) {
          $("#info").append('<p class="warning"> Error: ' + exception + '</p>');
        }
      }
    }
    
    webSocket.onmessage = function(msg) {
      $("#info").append('<p>From Server: ' + msg.data + '</p>');
    }
    
    webSocket.onclose = function() {
      $("#info").append('<p>Closed</p>');
    }
  } catch( exception ) {
    $("#info").append('<p>Error ' + exception + '.</p>');
  }
}

$(window).on("unload", function(e) {
  console.log("UNLOAD");
  if(webSocket && webSocket.readyState==1) {
    webSocket.close();
  }
});
</script>
EOF;

$h->banner = "<h1>Websocket Registered</h1>";

[$top, $footer] = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top   
  
<div id="info"></div>
$footer
EOF;


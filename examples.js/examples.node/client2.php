<?php
// BLP 2023-02-26 - use new approach
// Register with the websocket server (server3.php) running under node.

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->b_inlineScript =<<<EOF
'use strict';

var webSocket;

function connectWs() {
  try {
    // Note this was bartonlp.org but when I changed the server to use the info in
    // /etc/letsencrypt/live/www.bartonphillips.com I had to change the name below. Before I had a copy
    // of the encription keys in the same directory as the server3.php and this file. Not sure I
    // understand but it works.
    
    const host = 'wss://bartonphillips.com:8089/';

    webSocket = new WebSocket(host);

    webSocket.onopen = function() {
      $('#submit').text('Submit');
    }
    
    webSocket.onmessage = function(msg) {
      logWsMessage('<p class="message">From Server: ' + msg.data + '</p>');
    }
    
    webSocket.onclose = function() {
      logWsMessage('<p>Chat room Closed</p>');
    }
  } catch( exception ) {
    logWsMessage('<p>Error ' + exception + '.</p>');
  }
}

function isConnectedWs() {
  if(webSocket && webSocket.readyState==1) {
    return true;
  }
}

function sendWs() {
  let info = $('#info');
  
  if(isConnectedWs()) {
    let cmd = $('#cmd').val();
    
    if(cmd=='') {
      logWsMessage('<p class="warning">Cmd is required</p>');
      return;
    }

    try {
      webSocket.send(cmd);
    } catch(exception){
      logWsMessage('<p class="warning">Error: ' + exception + '</p>');
    }
  }
}

function logWsMessage(msg) {
  let info = $('#info');
  info.append('<p>' + msg + '</p>');
}

$(document).ready( function() {
  if(!('WebSocket' in window)) {
    $('<p>Oh no, you need a browser that supports WebSockets.</p>')
    .appendTo('#container');
  } else {
    connectWs();
  }

  $('#cmd').on("keypress", event => {
    if(event.keyCode == '13') {
      sendWs();
    }
  });

  $('#submit').on("click", function() {
    sendWs();
  });

  $('#disconnect').on("click", function() {
    if($(this).text() == "DISCONNECT") {
      webSocket.close();
      $(this).text("Connect");
    } else {
      connectWs();
      $(this).text("DISCONNECT");
    }
  });

  $(window).on ("unload", function(e) {
    console.log("UNLOAD");
    if(isConnectedWs()) {
      webSocket.send('Client Shut Down' );
      webSocket.close();
    }
  });
});
EOF;

$S->banner = "<h1>Test of Websockets</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<div id="container">
  <div id="info">
  </div>
  <div id="input">
    <table>
    <tr><th>Cmd</th><td><input id="cmd" type="text"></td></tr>
    </table>
    <button id="submit">Submit</button><br>
    <button id="disconnect">DISCONNECT</button>
  </div>
</div>
$footer
EOF;

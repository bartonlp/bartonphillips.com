<?php
// weewx-test2.php
// Uses the https://www.bartonphillips.com/node-watch/weewx.watch2.js
// weewx.watch2.js sets up a websocket server and watches ../weewx/index.php for a new version of
// the file. When it gets one it write to the 'connection' and says it has a new file.
// This program (weewx-test2.php) establishes a websocket client connection to the server and sends
// a message to the server so a connection is set up. Then it wait until it gets a message that
// there is a new file.
// We use an 'iframe' to hold the page contents. To do the communication between the main program
// and the 'iframe' they MUST be on the same domain (which they are now). I had this on HP-envy as
// an example but had to move it here.
// This does not need to be a php program it just saves a little typing.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
// set headFile to a local head.
$_site->headFile = 'head.php';
$_site->bannerFile = null;
$_site->footerFile = null;

$S = new $_site->className($_site);

$h->css = <<<EOF
<style>
body, html {width: 100%; height: 100%; margin: 0; padding: 0}
/* NOTE and iframe is default 'inline' */
iframe {display: block; width: 100%; height: 100%; border: none;}
</style>
EOF;

$h->script = <<<EOF
<script>
$(function($) {
  var wsUri = "wss://bartonlp.com:8080?BLP=8653"; // The BLP is used for authentication

  function testWebSocket() {
    websocket = new WebSocket(wsUri, 'watch'); // The server is 'watch'

    // These are the callbacks

    websocket.onopen = function(evt) {
      writeToScreen("CONNECTED");
      // We send HI to the server to estabish the connection.
      var jmsg = 'HI';
      writeToScreen("SENT: " + jmsg);
      websocket.send(jmsg);
    };

    // Notify us of a change. The server send a message when a new file is available.

    websocket.onmessage = function(evt) {
      let data = evt.data;
      console.log("DATA:", data);
      if(data.indexOf("RET") !== -1) {
        return;
      }
      $('iframe').attr("src", "https://www.bartonphillips.com/weewx/");
      $('iframe').on('load', function() {
        const frame = document.querySelector('iframe');
        frame.contentWindow.stopLoad();
      });
    };

    websocket.onclose = function(evt) {
      writeToScreen("DISCONNECTED");
    };

    websocket.onerror = function(evt) {
      writeToScreen('ERROR: ' + evt.data);
    };
  }

  // Write to console

  function writeToScreen(message) {
    console.log(message);
  }

  if (!('performance' in window)) {
    writeToScreen('No performance');
  }

  // At the beginning we fill the iframe with whatever is there.

  $('iframe').attr("src", "https://www.bartonphillips.com/weewx/");

  $('iframe').on('load', function() {
    const frame = document.querySelector('iframe');
    frame.contentWindow.stopLoad();
  });

  testWebSocket();
});
</script>
EOF;

$top = $S->getPageHead($h);

echo <<<EOF
$top
<body>
<iframe></iframe>
</body>
</html>
EOF;


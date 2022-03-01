#!/usr/bin/env node
/* This is a node.js program.
 * It is a websocket client and can run anywhere and will connect with
 * websocket-server.js if it is running on 'ws://bartonlp.org?BLP=8653'.
 */

var number;
var WebSocketClient = require('websocket').client;
var client = new WebSocketClient();

client.on('connectFailed', function(error) {
  console.log('Connect Error: ' + error.toString());
});

client.on('connect', function(connection) {
  console.log('WebSocket client connected');
  connection.on('error', function(error) {
    console.log("Connection Error: " + error.toString());
  });
  connection.on('close', function() {
    console.log('echo-protocol Connection Closed');
  });
  connection.on('message', function(message) {
    if (message.type === 'utf8') {
      console.log("Received: '" + message.utf8Data + "'");
    }
  });

  function sendNumber() {
    if (connection.connected) {
      //console.log("sendNumber connected");
      number = Math.round(Math.random() * 0xfffffffffff);

      connection.sendUTF(JSON.stringify({event: 'hello',
                                        siteId: 'ALL',
                                        prog: number.toString()
      }));
      setTimeout(sendNumber, 1000);
    }
  }
  sendNumber();
});

// muse have the ?BLP=8653 as that is the key to letting this run.
client.connect('wss://bartonlp.org:8089?BLP=8653', 'slideshow');

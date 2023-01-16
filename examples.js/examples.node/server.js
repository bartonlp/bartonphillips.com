'use strict';
   
const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');

const server = https.createServer({
  cert: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/fullchain.pem'),
  key: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/privkey.pem')

//  cert: fs.readFileSync('./fullchain9.pem'),
//  key: fs.readFileSync('./privkey9.pem')
});

const wss = new WebSocket.Server({ server });

wss.on('connection', function connection(ws) {
  ws.on('message', function message(msg) {
    console.log('received: %s', msg);
    wss.clients.forEach(function (client) {
      if (client.readyState == WebSocket.OPEN) {
        console.log("sending msg: " + msg);
        client.send( msg.toString() );
      }
    });
  });

  console.log("sending: Chat room is working!");
  ws.send('Chat room is working!');
});

server.listen(8089);

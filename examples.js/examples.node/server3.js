'use strict';

const fs = require('fs');
const https = require('https');
const WebSocket = require('ws'); // https://github.com/websockets/ws
const glob = require('glob'); // https://github.com/isaacs/node-glob
const path = require('path');

const server = new https.createServer({
  // To use the certs in /etc/letsencrypt/live/www.bartonphillips.com
  // I had to change the mode of the directories ..../live and
  // ..../archive to g+r for group root. Not entierly sure why this is
  // needed. It may have to do with the fact that other users can be
  // part of roots user-list.
  cert: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/fullchain.pem'),
  key: fs.readFileSync('/etc/letsencrypt/live/www.bartonphillips.com/privkey.pem')
//  cert: fs.readFileSync('./fullchain9.pem'),
//  key: fs.readFileSync('./privkey9.pem')
});

let inx = 0, c = [];
let matches = [];
let minx = 0;

const wss = new WebSocket.Server({ server });

wss.on('connection', function connection(ws) {
  console.log("inx: "+inx);

  console.log("OPEN");
  ws.send('All glory to WebSockets!');

  ws.inx = inx;
  c[inx++] = ws;
  
  ws.on('message', function message(x) {
    let msg = JSON.parse(x.toString());
    let reply;
    
    console.log("ws.inx: " + ws.inx + ", msg", msg);
    
    switch(msg.msg) {
      case 'reg':
        ws.cmd= 'reg';
        reply = "Registered for ALL";
        ws.send(reply);
        return;
      case 'imgstart':
        let pattern = "/var/www/html/photos/*.JPG";
        let m = glob.sync(pattern);
        console.log("m:", m);
        matches = []; // init it
        m.forEach((p)=> { matches.push(path.basename(p))});
        console.log("matches: ", matches);
        var matches_iterator = matches.entries();
        console.log("iter: " , matches_iterator);
        reply = "Image Start Done";
        ws.send(reply);
        break;
      case 'img':
        if(minx >= matches.length) minx = 0;
        let file = matches[minx++];
        console.log("file: ", file);
        reply = "<img style='width: 200px' src='https://bartonlp.org/photos/"+file+"'>";
        ws.send(reply);
        break
      case 'start':
        clearInterval(ws.num);
        ws.num = setInterval(sendNumber, 10000, ws);
        reply = "Starting numbers="+ws.num;
        ws.send(reply); // Send to the one that sent it.
        break;
      case 'stop':
        reply = "Stopping numbers="+ws.num;
        ws.send(reply);
        clearInterval(ws.num);
        break;
      default:
        console.log("didn't find a match: msg=", msg.msg);
        break;
    }

    for(let i=0; i<c.length; ++i) {
      if(c[i].cmd == "reg") {
        console.log("Message to c[i]:"+c[i].inx);
        c[i].send("i="+i+", msg: " + reply); // Send to anyone who registered ALL
      }
    }
  });

  ws.on('close', () => {
    console.log("CLOSING");
    
    for(let i=0; i < inx; ++i) {
      console.log("Array index: "+i+", "+ 
            ", inx: "+c[i].inx);

      if(c[i].inx == ws.inx) {
        console.log("stop interval: "+c[i].num);
        clearInterval(c[i].num);
        console.log("Removed: inx: "+ c[i].inx);
        c.splice(i--,1);
        --inx;
      } else {
        // re-index the remaining items
        c[i].inx = i;
      }
    }
  });
});

server.listen(8089, () => {
  console.log("Listining on port 8089");
});

function sendNumber(ws) {
  let number = Math.round(Math.random() * 0xfffffffffff);
  console.log("sendNumber for "+ws.num+ ": " + number);
  ws.send(number.toString());
  for(let i=0; i<c.length; ++i) {
    if(c[i].cmd == "reg") {
      console.log("REG -- inx: " +ws.inx);
      c[i].send("i="+i+", from: " +ws.inx + ", number: " + number.toString());
    }
  }
}


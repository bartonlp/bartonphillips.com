// weewx.watch2.js
// The program weewx-test2.php uses this program.
// This program and the iframe (weewx/index.php) must be on the same
// domain.
// This is a node program. It watches $DOCUMENT_ROOT/weewx/index.php to see when it is
// updated and then uses websockets to push notification to the client.

const Gaze = require('gaze').Gaze,
fs = require('fs'),
WebSocketServer = require('websocket').server, // websocket: npm install websocket
http = require('https'),
w = "/var/www/bartonphillips.com/weewx/index.php",
watch = new Gaze(w);

// Start watching. If no connection we don't do scripts.

watch.on('changed', scripts);

var httpsOptions = {
  key: fs.readFileSync('./ssl/privkey.pem'),
  cert: fs.readFileSync('./ssl/fullchain.pem')
};

var server = http.createServer(httpsOptions, function(request, response) {
  console.log('Received request for ' + request.url + " from: " +
        request.connection.remoteAddress);
  response.writeHead(403); // Forbiden
  response.end("<h1>403 Forbiden</h1>"+
               "<h2>Go Away</h2>"+
               "<p>This port is used for WebSockets. "+
               "You should run one of the 'websocket-...html' files from "+
               "http://www.bartonlp.com/examples.node/websocket-...html.</p>");
});

// Start listening on port 8080

var connection;

server.listen(8080, function() {
  console.log('Websocket-server is listening on port 8080');
});

// Create the websocket server

var c = [], inx = 0;

wsServer = new WebSocketServer({
  httpServer: server,
  autoAcceptConnections: false
});

// Check the origin of the connection

function originIsAllowed(origin, r) {
  console.log("r:", r, origin)
  if(r.BLP == '8653') return true;
  return false;
}

wsServer.on('request', function(request) {
  if(!originIsAllowed(request.origin, request.resourceURL.query)) {
    // Make sure we only accept requests from an allowed origin
    request.reject();
    console.log('Connection from origin ' + request.origin + ' rejected.\n');
    return;
  }

  console.log("request OK");
  
  // Catch the NO protocal exception

  try {
    connection = request.accept('watch', request.origin);
    console.log(`Connection ${inx} OK, started Watch: ${new Date()}`);
  } catch(e) {
    console.log("request.accept Error: "+ e);
    return false;
  }

  connection.inx = inx;
  c[inx++] = connection; // Add to connections array

  console.log("connection", connection);
  
  connection.on('message', function(data) {
    console.log(`message: ${data.utf8Data}`);
    connection.sendUTF(`RET inx: ${inx -1}`);
  });
  
  connection.on('close', function(reasonCode, description) {
    console.log('Disconnected: Code: '+reasonCode+
                ', Desc: '+description+
                '\n\tPeer: ' + connection.remoteAddress
               );

    for(var i=0; i < inx; ++i) {
      if(c[i].inx == connection.inx) {
        console.log("\tRemoved: inx: "+ c[i].inx);

        c.splice(i--, 1);
        --inx;
      } else {
        // re-index the remaining items
        c[i].inx = i;
      }
    }
  });
});

function scripts(file) {
  console.log(`CHANGED file: ${file}`);

  for(let con of c) {
    if(con) {
      let timestamp = new Date();
      console.log(`inx: ${con.inx}. Got data ${timestamp}`);
      con.sendUTF(`inx: ${con.inx}. Got New Data ${timestamp}`);
    }
  }
}

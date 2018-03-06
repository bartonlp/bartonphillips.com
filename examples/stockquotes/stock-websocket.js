// stock-websocket.js
// Get stock info from iex

const fs = require('fs'),
rp = require('request-promise'), // don't name this 'response' as there are too many of those
WebSocketServer = require('websocket').server, // websocket: npm install websocket
mysql = require('promise-mysql2'),
http = require('https');

const query = function(sql, value) {
  // First we must return from createConnection()

  return mysql.createConnection({
    // We are using LOCALHOST not bartonlp.com!
    host: "localhost",
    user: "barton",
    password: "7098653",
    database: "barton"
  }).then(conn => {
    // Now we need to return from query
    return conn.query(sql, value).then(([rows]) => {
      conn.end();
      // rows can be taken appart 'rows[n].fieldKey' or
      // for(let d of rows) {
      //   for(let [key, val] of Object.entries(d)) {
      //     key, val //are the key and value
      //   }
      // }

      return rows; // This returns the a 'RowDataPacket' array
    });
  });
}

// use the keys at /var/www/bartonphillips.com

var httpsOptions = {
  key: fs.readFileSync('/var/www/bartonphillips.com/node-watch/ssl/privkey.pem'),
  cert: fs.readFileSync('/var/www/bartonphillips.com/node-watch/ssl/fullchain.pem')
};

var server = http.createServer(httpsOptions, function(request, response) {
  console.log('Received request for ' + request.url + " from: " +
              request.connection.remoteAddress);
  response.writeHead(403); // Forbiden
  response.end("<h1>403 Forbiden</h1>"+
               "<h2>Go Away</h2>"+
               "<p>This port is used for WebSockets.");
});

// Start listening on port 8080

var connection;
const port = 3000;
server.listen(port, function() {
  console.log('Websocket-server is listening on port ' + port);
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
    connection = request.accept('stocks', request.origin);
    console.log(`Connection ${inx} OK: ${new Date()}`);
  } catch(e) {
    console.log("request.accept Error: "+ e);
    return false;
  }

  connection.inx = inx;
  c[inx++] = connection; // Add to connections array

  //console.log("connection", connection);

  connection.on('message', function(data) {
    console.log(`message: ${data.utf8Data}`);
    connection.sendUTF(`RET inx: ${inx -1}`);
    getStockInfo(connection); // This is an object. Always send the responce to 'Hi'
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

// We send info to all of the connected clients every 5min.

setInterval(function() {
  if(c.length == 0) return; // No one connected
  
  getStockInfo(c); // This is an array
}, 300000);

// Get Stock Info
// This always gets the first reading. Then it looks to see if 'con' is
// an array, which it will be except the first time after receiving the
// 'Hi' from the client. If 'con' is an array we check the time of day
// and the day of the week to see if we should continue.

function getStockInfo(con) {
  let date = new Date();
  let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
  let day = date.getDay(); // we only take readings during the week not Sat. or Sun.
  // If con is an array that means that it is 'c' not 'connection'.
  // Check the time and the day of week.
  if(con instanceof Array && ((time > 21 || time < 14) || (day == 6 || day == 7))) return;

  query("select stock from stocks.stocks")
  .then(data => {
    // put the sql info into var.
    let val = [];
    for(let ar of data) {
      for(let d of Object.values(ar)) {
        val.push(d);
      }
    }
    return val;
  })
  .then(data => {
    // Send the data from sql stocks to iex
    let prefix = "https://api.iextrading.com/1.0";
    let url = prefix + "/stock/market/batch?symbols=" + data.join(',') + "&types=quote";
    // return the results
    return rp(url);
  })
  .then(data => {
    // take the iex results and make a smaller array
    let ar = {};
    let d = JSON.parse(data);
    let t, st;
    for(let dd of Object.values(d)) {
      t = dd.quote;
      st = t.symbol;
      ar[st] = {price: t.latestPrice, vol: t.latestVolume, change: t.change, chper: t.changePercent};
    }
    return ar;
  })
  .then(data => {
    var dataStr = JSON.stringify(data);

    // If 'con' is an array this is after the initial connection.
    if(con instanceof Array) {
      for(let c of con) {
        if(c) {
          c.sendUTF(dataStr);
        }
      } 
    } else {
      // This is from 'message'
      con.sendUTF(dataStr);
    }
  })
  .catch(err => console.log("ERR:", err));
};

// stock-websocket-sync.js
// This version uses 'sync-mysql' and 'sync-requst' to do the sql query
// and 'request'. It is much simpler!
// Get stock info from iex

const fs = require('fs'),
req = require('sync-request'), // does a synchronous request
MySql = require('sync-mysql'), // does a synchronous mysql
WebSocketServer = require('websocket').server, // websocket: npm install websocket
http = require('https');

const sql = new MySql({
  host: 'localhost',
  user: 'barton',
  password: '7098653'
});

// use the keys at /var/www/bartonphillips.com

var httpsOptions = {
  key: fs.readFileSync('/var/www/bartonphillips.com/examples/weewx-watch/ssl/privkey.pem'),
  cert: fs.readFileSync('/var/www/bartonphillips.com/examples/weewx-watch/ssl/fullchain.pem')
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

  // Use a synchronous query
  
  let rows = sql.query("select replace(stock, '-BLP', '') as stock from stocks.stocks "+
                       "where status not in('mutual','watch','sold')");
  
  rows = rows.map(x => {
    if(x.stock == "RDS-A") x.stock = "RDS.A";
    return x.stock
  });
  
  // Send the data from sql stocks to iex

  const url = "https://api.iextrading.com/1.0/stock/market/batch?symbols=" +
              rows.join(',') + "&types=quote";

  const data = req('GET', url).getBody('utf8'); // sync-request()
  let ar = {};
  let d = JSON.parse(data);
  let t, st;
  for(let dd of Object.values(d)) {
    t = dd.quote;
    st = t.symbol;
    ar[st] = {price: t.latestPrice, vol: t.latestVolume, change: t.change, chper: t.changePercent};
  }
  var dataStr = JSON.stringify(ar);

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
};

/* This is the iex info.

[quote] => stdClass Object
  (
    [symbol] => BP
    [companyName] => BP p.l.c.
    [primaryExchange] => New York Stock Exchange
    [sector] => Energy
    [calculationPrice] => close
    [open] => 39.65
    [openTime] => 1518791506171
    [close] => 39.62
    [closeTime] => 1518814834937
    [high] => 40.02
    [low] => 39.51
    [latestPrice] => 39.62
    [latestSource] => Close
    [latestTime] => February 16, 2018
    [latestUpdate] => 1518814834937
    [latestVolume] => 4661403
    [iexRealtimePrice] => 
    [iexRealtimeSize] => 
    [iexLastUpdated] => 
    [delayedPrice] => 39.99
    [delayedPriceTime] => 1518817832033
    [previousClose] => 39.84
    [change] => -0.22
    [changePercent] => -0.00552
    [iexMarketPercent] => 
    [iexVolume] => 
    [avgTotalVolume] => 6823822
    [iexBidPrice] => 
    [iexBidSize] => 
    [iexAskPrice] => 
    [iexAskSize] => 
    [marketCap] => 131608767299
    [peRatio] => 21.07
    [week52High] => 44.615
    [week52Low] => 33.1
    [ytdChange] => -0.06512505899009
  )
)
*/        
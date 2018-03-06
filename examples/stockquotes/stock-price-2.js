// stock-price-2.js
// This is used by stock-price-2.php
// Uses 'websocket' via wss://bartonlp.com:3000?BlP-8653
// There are two nodejs implementations of the websocket server,
// stock-websocket.js and stock-websocket-sync.js. One uses Promises
// and the other uses all synchronous functions.

const wsUri = "wss://bartonlp.com:3000?BLP=8653"; // The BLP is used for authentication

function testWebSocket() {
  websocket = new WebSocket(wsUri, 'stocks'); // The server is 'stocks'

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
    if(data.indexOf("RET") !== -1) {
      console.log(data);
    } else {
      data = JSON.parse(data);
      let div = document.querySelector("#stock-data");

      let str = "<p>" + new Date() + "</p>";
      str += `
             <table border='1'>
                           <thead>
                           <tr><th>Stock</th><th>Price</th><th>Volume</th><th>Change</th><th>% Change</th><tr>
                           </thead>
                           <tbody>
                           `;
      let price, vol, change, per;

      for(let [k, d] of Object.entries(data)) {
        price = d.price.toLocaleString();
        vol = d.vol.toLocaleString();
        change = d.change.toLocaleString();
        per = d.chper * 100;
        per = per.toLocaleString();
        
        str += `<tr><td>${k}</td><td>${price}</td><td>${vol}</td><td>${change}</td><td>${per}</td></tr>`;
      }
      str += "</tbody></table>";
      div.innerHTML = str;
    }
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

testWebSocket();

// Scrape the Wall Street Journal for DJI and Change

setInterval(getDji, 300000);

function getDji(start=false) {
  let date = new Date();
  let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
  let day = date.getDay(); // we only take readings during the week not Sat. or Sun.
  // If con is an array that means that it is 'c' not 'connection'.
  // Check the time and the day of week.
  if((start === false) && ((time > 21 || time < 14) || (day == 6 || day == 7))) {
    return;
  }
  
  $.ajax({
    url: "stock-price-2.php",
    data: {page: 'dow'},
    success: (data) => {
      data = JSON.parse(data);
      $("#dji").html(`<h3>Dow Jones Average: ${data.dji}, Change: ${data.change}</h3>`);
    },      
    error: (err) => console.log(err)
  });  
};

getDji(true);
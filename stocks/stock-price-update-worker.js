// stock-price-update-worker.js
// This is a javascript 'worker' that goes with stock-price-update.php,
// and stock-price-update.js
// BLP 2018-03-15 -- Add logic (below) to figure out daylight savings
// time.

'use strict';

// Is this daylight savings time?

Date.prototype.stdTimezoneOffset = function() {
  var jan = new Date(this.getFullYear(), 0, 1); // Jan 1. NOTE Month starts at zero
  var jul = new Date(this.getFullYear(), 6, 1); // July 1
  return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
}

// Add a Date prototype for dst()

Date.prototype.dst = function() {
  return this.getTimezoneOffset() < this.stdTimezoneOffset();
}

var orgStock;

setInterval(getInfo, 300000); // Five Min.

getInfo(true);

function getInfo(start=false) {
  let date = new Date();
  let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
  let day = date.getDay(); // we only take readings during the week not Sat. or Sun.
  if(date.dst()) { // BLP 2018-03-15 -- if dst add 1 hour so 14 and 21 UTC are still correct.
    ++time;
  }
  // If con is an array that means that it is 'c' not 'connection'.
  // Check the time and the day of week.
  if(start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7))) return;

  //console.log("start:", start, " date:", date);
  
  query().then(data => {
    // data is an object with r1 and r2. r1 is the DDAIF data and r2 is
    // the 'stocks' and the DJI data
    let r1 = data.r1;
    let r2 = data.r2;

    // Get Dow Joins data.
    
    let dji = r2.dji,
    djichange = r2.change,
    djipercent = r2.per,
    djidate = r2.date;

    // I want orgStock to be a new variable that is not changed when I do
    // the below map. This is the original data.r2.stocks.
    
    orgStock = JSON.parse(JSON.stringify(r2.stocks));
    
    let rows = r2.stocks.map(x => {
      if(x[0] == "RDS-A") {
        return x[0] = "RDS.A";
      }
      return x[0];
    });

    // Send the data from sql stocks to iex

    let t = rows.join(',');
    
    const url = "https://api.iextrading.com/1.0/stock/market/batch?symbols=" +
                t + "&types=quote";

    // In lieu of AJAX. This does a GET from url.
    
    fetch(url)
        .then(data => data.text())
        .then(data => {
      let ar = {};
      let d = JSON.parse(data);

      // orgStock is from above, it is the value from the data.stocks
      // from the query().
      
      for(let stocks of orgStock) {
        // The stocks array has [0] = stock, [1] = orgPrice, [2] = qty,
        // [3] = status, [4] = company, [5] = avVol, [6] = avPrice
        
        let t, st = stocks[0], orgPrice = stocks[1] || 0, qty = stocks[2] || 0,
        status = stocks[3] || 0, company = stocks[4], avVol = stocks[5], avPrice = stocks[6];

        // NOTE IEX wants RDS-A to be RDS.A!

        let stx = st == 'RDS-A' ? 'RDS.A' : st;

        let curPrice, curVol, curChange, curPercent, curUpdate;
        
        if(typeof d[stx] == 'undefined') {
          if(stx == "DDAIF") {
            curPrice = r1.curPrice;
            curVol = r1.curVol;
            curChange = r1.curChange;
            curPercent = r1.curPercent;
            curUpdate = r1.curUpdate;
          } else {
            continue;
          }
        } else {
          t = d[stx].quote; // get the quote from data.
          curPrice = t.latestPrice;
          curVol = t.latestVolume;
          curChange = t.change;
          curPercent = t.changePercent;
          curUpdate = t.latestUpdate;
        }
        
        // st is the stock sym with the RDS-A
        
        ar[st] = {
          orgPrice: orgPrice, qty: qty, status: status, company: company, avVol: avVol,
          avPrice: avPrice, price: curPrice, vol: curVol, change: curChange,
          chper: curPercent, last: curUpdate
        };
      }

      // Turn this into json.
      
      var dataStr = JSON.stringify([ar, dji, djichange, djipercent, djidate]);

      // And send it to the client.

      postMessage(dataStr);
    })
  }).catch(err => console.log("err:", err));
};

// query()
// This uses 'fetch() to do a GET and a POST with form like data.
// This is an 'async' function

async function query() {
  // Again this is like AJAX. Both calls return JSON data.

  // First do a GET for the Daimler data.
  
  let r1 = await fetch("./stock-price-update.php?WSJ=true").then(data => data.json());

  // Then do a POST for stocks info and DJIA
  
  let r2 = await fetch("./stock-price-update.php", {
    body: "page=web", // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json());

  return {r1: r1, r2: r2};
};

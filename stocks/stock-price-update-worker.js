// stock-price-update-worker.js
// This is a javascript 'worker' that goes with stock-price-update.php,
// and stock-price-update.js
// BLP 2018-03-15 -- Add logic (below) to figure out daylight savings
// time.
// BLP 2020-06-04 -- Get the day200MovingAvg and TotalVolume from iex.
// BLP 2020-09-17 -- Remove r1 from query();
// BLP 2020-09-28 -- Upgraded to iex paid plan. Same key.

// This is for iex cloud the new way to get stock info.
// API Token: pk_feb2cd9902f24ed692db213b2b413272 
// Account No. 7e36c73b36687b93ac549e7f828447c2 
// SECRET sk_6f07cb9018994f51a6d27eb7b27d5ebf

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

// Define the iex token key.
var iex_token = 'pk_feb2cd9902f24ed692db213b2b413272';

var orgStock;

// Set timer for five minutes.

setInterval(getInfo, 300000); // Five Min.

// Do this at least once. The function will be called every five
// minutes hereafter.

getInfo(true); // pass true as start.

function getInfo(start=false) {
  // Get today's date
  
  let date = new Date();

  // Get time and day
  
  let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
  let day = date.getDay(); // we only take readings during the week not Sat. or Sun.

  //console.log("day: "+day+", time: "+time);
  
  if(date.dst()) { // BLP 2018-03-15 -- if dst add 1 hour so 14 and 21 UTC are still correct.
    ++time;
  }

  // Check the time and the day of week. We only want to do this from
  // 9am to 4pm. Note time is UTC or Grenich time

  if(start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7))) return;

  //console.log("start:", start, ", time:", time, " day: ", day, ", date:", date);
  
  query().then(data => {
    // BLP 2020-09-17 -- 
    // data is an object with r1 and r2. r1 is the DDAIF data and r2 is
    // the 'stocks' and the DJI data
    //let r1 = data.r1;
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

    // BLP 2020-04-02 -- This uses the NEW cloud version ipxapis.com 
    // BLP 2020-06-04 -- add filter and avgTotalVolume and
    // day200MovingAvg.
    
    const url = "https://cloud.iexapis.com/stable/stock/market/batch?symbols="+t+
                "&types=quote,stats&filter=latestPrice,latestVolume,change,changePercent,latestUpdate,"+
                "avgTotalVolume,day200MovingAvg"+
                "&token=" + iex_token;

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
        status = stocks[3] || 0, company = stocks[4]; 

        // NOTE IEX wants RDS-A to be RDS.A!

        let stx = st == 'RDS-A' ? 'RDS.A' : st;

        let curPrice, curVol, curChange, curPercent, curUpdate;
        let avPrice, avVol;

        t = d[stx].quote; // get the quote from data.
        curPrice = t.latestPrice;
        curVol = t.latestVolume;
        curChange = t.change;
        curPercent = t.changePercent;
        curUpdate = t.latestUpdate;

        // BLP 2020-06-04 -- 
        avVol = t.avgTotalVolume;
        avPrice = d[stx].stats.day200MovingAvg;
        
        // st is the stock sym with the RDS-A
        
        ar[st] = {
          orgPrice: orgPrice, qty: qty, status: status, company: company,
          price: curPrice, vol: curVol, change: curChange,
          chper: curPercent, last: curUpdate,
          // BLP 2020-06-04 -- 
          avPrice: avPrice, avVol: avVol
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
// BLP 2020-09-17 -- remove the r1 fetch(). Now we ONLY return r2 which
// has all the stocks from stocks.stocks, the dji value, change,
// changePer and dateTime.

async function query() {
  // Again this is like AJAX. Both calls return JSON data.
  
  let r2 = await fetch("./stock-price-update.php", {
    body: "page=web", // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json());

  return {r2: r2};
};

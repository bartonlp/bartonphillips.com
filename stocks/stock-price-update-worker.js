// stock-price-3-worker.js

var orgStock;

setInterval(getInfo, 300000); // Five Min.

getInfo(true);

function getInfo(start=false) {
  let date = new Date();
  let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
  let day = date.getDay(); // we only take readings during the week not Sat. or Sun.
  // If con is an array that means that it is 'c' not 'connection'.
  // Check the time and the day of week.
  if(start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7))) return;

  //console.log("start:", start, " date:", date);
  
  query().then(data => {
    let dji = data.dji,
    djichange = data.change,
    djipercent = data.per,
    djidate = data.date;

    // I want orgStock to be a new variable that is not changed when I do
    // the below map. This is the original data.stocks.
    
    orgStock = JSON.parse(JSON.stringify(data.stocks)); 
        
    let rows = data.stocks.map(x => {
      if(x[0] == "RDS-A") {
        return x[0] = "RDS.A";
      }
      x[0] = x[0].replace(/-BLP/, '');
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
        
        let stx = st.replace(/-BLP/, '').replace(/RDS-A/, 'RDS.A');

        t = d[stx].quote; // get the quote from data.

        // st is the stock sym with the -BLP and RDS-A
        
        ar[st] = {
          orgPrice: orgPrice, qty: qty, status: status, company: company, avVol: avVol,
          avPrice: avPrice, price: t.latestPrice, vol: t.latestVolume, change: t.change,
          chper: t.changePercent, last: t.latestUpdate
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
// This uses 'fetch() to do a POST with form like data.

function query() {
  // Again this is like an AJAX POST.
  
  return fetch("./stock-price-update.php", {
    body: "page=web", // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json()); // 'stock-price-3.php' POST returns json data.
};

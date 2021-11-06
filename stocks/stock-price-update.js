// stock-price-update.js
// This is used by stock-price-update.php
// BLP 2021-11-04 -- Removed stock-price-update-worker.js. Now all of
// the work is done here and in the AJAX in stock-price-update.php
// which uses the IPX secret from a secure location.
// BLP 2020-06-04 -- changed the php and worker files to use ipx to get
// 200dayMovingAvg and avgTotalVolume. No changes were required to this
// program
// BLP 2020-10-21 -- include mutual funds in the active items.

'use strict';

var str, accTotal = 0, accDiff = 0;

// Put the select text into the 'selectstatus' div

let msg = `
<p>You can select which status to show:
<select>
  <option>ALL</option>
  <option selected>active</option>
  <option>watch</option>
  <option>sold</option>
  </select>
</p>
`;

$("#selectstatus").html(msg);

Date.prototype.stdTimezoneOffset = function () {
  var jan = new Date(this.getFullYear(), 0, 1);
  var jul = new Date(this.getFullYear(), 6, 1);
  return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
}

Date.prototype.dst = function() {
  return this.getTimezoneOffset() < this.stdTimezoneOffset();
}

setInterval(getInfo, 300000); // Five Min.

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
    let r2 = data.r2;

    // Get Dow Joins data.

    let djiAv = r2.dji,
    djiChange = r2.change,
    djiPercent = r2.per,
    djiDate = r2.date;

    //console.log("DJI Date: " + djiDate);

    str = `
<h2><span class='small'>Last Update: ${djiDate}<br></span>
Dow Jones Average: $${djiAv},
Change: <span class='posNeg'>$${djiChange}</span>,
<span class='posNeg'>
${djiPercent}</span>
</h2>
`;

    $("#dji").html(str);

    if(djiChange < 0) {
      $(".posNeg").css('color', 'red');
    } else {
      $(".posNeg").css('color', 'black');
    }

    str = `            
<table border="1" id="stocks">
<thead>
<tr><th>Stock</th><th>Price</th><th>Av Price</th><th>Buy Price<br>% Diff</th><th>Qty<br>Value</th><th>Volume<br>Av Vol</th><th>Change<br>% Change</th><th>Status</th></tr>
</thead>
<tbody>
`;

    // I want orgStock to be a new variable that is not changed when I do
    // the below map. This is the original data.r2.stocks.

    let orgStock = r2.stocks; //Object.entries(r2.stocks);

    // Send the data from sql stocks to iex

    let ar = [];

    for(const stocks in orgStock) {
      let st = stocks;
      let s = orgStock[stocks];

      let orgPrice = s.price || 0, qty = s.qty || 0,
      status = s.status || 0, company = s.company; 

      let curPrice, curVol, curChange, curPercent, curUpdate;
      let avPrice, avVol;

      curPrice = s.latestPrice || 0;
      curVol = s.latestVolume;
      curChange = s.change || 0;
      curPercent = s.changePercent;
      curUpdate = s.latestUpdate;

      avVol = s.avgTotalVolume;
      avPrice = s.moving || 0;

      console.log("st: " + st + ", change: " + curChange + ", curPrice: " + curPrice + ", avPrice: " + avPrice);

      if(status == 'active' || status == 'mutual') {
        //console.log("stock: %s, price: %f, qty: %d", k, v.price, parseInt(v.qty));
        accTotal += curPrice * qty;
        accDiff += curChange * qty;
      }

      // Create value from qty times price
      let value = (qty * orgPrice).toLocaleString(undefined, {style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2, maximumFractionDigits: 2});

      // Create orgPer from (price - orgPrice)/orgPrice

      let orgPer = ((curPrice - orgPrice) / orgPrice)
      .toLocaleString(undefined, {style: 'percent',
      minimumFractionDights: 2, maximumFractionDigits: 2});

      // If orgPer is neg make it red. 

      if(orgPer.indexOf('-') !== -1) {
        orgPer = `<span class='neg'>${orgPer}</span>`;
      }

      // Take data[0] appart into price, qty, avPrice, change, percent,
      // volume, avgVolume, moving
  
      let price = curPrice.toLocaleString(undefined, {style: 'currency',
      currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2});

      let vol = (curVol == null ? 0 : curVol).toLocaleString();

      let change = curChange.toLocaleString(undefined, {style: 'currency', currency: 'USD',
      minimumFractionDigits: 2, maximumFractionDigits: 2});

      let percent = (curPercent == null ? 0 : curPercent).toLocaleString(undefined, {style: 'percent',
      minimumFractionDigits: 2, maximumFractionDigits: 2});

      orgPrice = parseFloat((orgPrice == null ? 0 : orgPrice), 10).toLocaleString(undefined, {style: 'currency', currency: 'USD',
      minimumFractionDigits: 2, maximumFractionDigits: 2});
      
      qty = qty.toLocaleString();

      company =company.toLowerCase();
      
      avVol = (avVol == null ? 0 : avVol).toLocaleString();

      let movingPer = ((curPrice - avPrice) /avPrice).toLocaleString(undefined, {style: 'percent',
      minimumFractionDigits: 2, maximumFractionDigits: 2});

      avPrice = avPrice.toLocaleString(undefined, {style: 'currency',
      currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2});

      // If Change/% Change is negitive make it red

      if(change.indexOf('-') !== -1) {
        change = `<span class='neg'>${change}</span>`;
        percent = `<span class='neg'>${percent}</span>`;
      }

      // If movingPer is neg make it red.

      if(movingPer.indexOf('-') !== -1) {
        movingPer = `<span class='neg'>${movingPer}</span>`;
      }
    
      // Is todays day the same as last? If yes then this is a 'current'
      // volume otherwise it is from the close.
    
      if(new Date(curUpdate).toDateString() == new Date().toDateString()) {
        vol = '<span class="current">' + vol + '</span>';
      }

      // Make the row
    
      str += `<tr><td><span>${st}</span><br><span>${company}</span></td><td>${price}</td>
<td>${avPrice}<br>${movingPer}</td><td>${orgPrice}<br>${orgPer}</td>
<td>${qty}<br>${value}</td><td>${vol}<br>${avVol}</td><td>${change}<br>${percent}</td><td>${status}</td></tr>`;
    }

    // Now Make totals row

    str += `
    </tbody>
    </table>
    <table id='totals'>
      <tr><th>Diff:</th><th>${accDiff.toLocaleString(undefined, {style: 'currency', currency: 'USD',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</th></tr>
      <tr><th>Total:</th><th>${accTotal.toLocaleString(undefined, {style: 'currency', currency: 'USD', 
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</th></tr>
    </table>
`;

    // Render the data to 'stock-data'. This will remove the 'wait' logo

    $("#stock-data").html(str);

    $("#attribution").html('<a href="https://iexcloud.io">Data provided by IEX Cloud</a>');

    // If we Click on the first field which is the stock name. This takes you
    // to marketwatch.com
  
    $("body").on("click", "#stocks td:first-child", function(e) {
      // the td is stock and company each in a span. The stock is the
      // first span.
      var stk = $('span:first-child', this).text();

      if(stk == 'RDS-A') stk = "RDS.A";

      var url = "https://www.marketwatch.com/investing/stock/"+stk;
      var w1 = window.open(url, '_blank');
      return false;
    });

    // Hide All rows then look at status to tell what to do.
    // Only when the worker provides new data.

    var status = $("#selectstatus select").val();
    //console.log("New status:", status);

    $("#stocks tbody tr").hide();

    $("#stocks tbody tr").each(function() {
      let stat = $("td:last-child", this).text();
      if(status == 'active' && stat == 'mutual') {
        $(this).closest('tr').show();
      } else {
        if(status == stat) {
          $(this).closest('tr').show();
        }
      }
    });

    $("body").on('change', "#selectstatus select", function(e) {
      let sel = $(this).val();
      //console.log("change: "+sel);

      switch(sel) {
        case 'sold':
          $("#stocks thead th:nth-child(4)").html("Sell Price<br>% Diff");
          break;
        case 'watch':
          $("#stocks thead th:nth-child(4)").html("Watch Price<br>% Diff");
          break;
        case 'active':
        case 'ALL':
          $("#stocks thead th:nth-child(4)").html("Buy Price<br>% Diff");
          break;
      }      

      let tr = $("#stocks tbody tr");

      if(sel == 'ALL') {
        tr.show();
      } else {
        tr.hide();
        let status = $("#stocks td:last-child"); // status

        status.each(function() {
          // BLP 2020-10-21 -- a mutual fund and sel is active.

          if(sel == 'active' && $(this).text() == 'mutual') {
            $(this).closest('tr').show();
          } else {
            if(sel == $(this).text()) {
              $(this).closest('tr').show();
            }
          }
        });
      }
    });
  });
};

async function query() {
  // Again this is like AJAX.
  // BLP 2021-11-04 -- stock-price-update.php does the IPX logic and
  // uses a secure secret token.

  let r2 = await fetch("./stock-price-update.php", {
    body: "page=web", // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json());

  return {r2: r2};
};

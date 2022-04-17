// stock-price-update.js
// This is used by stock-price-update.php

'use strict';

let str;

let formatNumber = new Intl.NumberFormat(undefined, {
  // These options are needed to round to whole numbers if that's what you want.
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});
  
let formatMoney = new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'USD',

  // These options are needed to round to whole numbers if that's what you want.
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});

let formatPercent = new Intl.NumberFormat(undefined, {
  style: 'percent',
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});

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

setInterval(getInfo, 300000); // Five Min. NOTE getinfo(start) is false if not present which is the default.

getInfo(true); // pass true as start.

// start will be true the first time from the above call, but will be
// false from every call by setInterval().

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

  query('web').then(data => {
    // Get Dow Joins data.

    //console.log("data:", data);
    
    let djiAv = data.dji,
    djiChange = data.change,
    djiPercent = data.per,
    djiDate = data.date;

    console.log("DJI Date: " + djiDate);

    str = `
<h2><span class='small'>Last Update: ${djiDate}<br></span>
<a target='_blank' href='https://www.marketwatch.com/investing/index/djia'>Dow Jones Average: $${djiAv}</a>,
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
<tr><th>Stock</th><th>Price</th><th>Av Price</th><th>Buy Price<br>% Diff</th><th>Qty<br>Value</th><th>Av Vol</th><th>Change<br>% Change</th><th>Status</th></tr>
</thead>
<tbody>
`;

    // I want orgStock to be a new variable that is not changed when I do
    // the below map. This is the original data.stocks.

    let orgStock = data.stocks; 

    //console.log("orgStock: ", orgStock);
    
    // Send the data from sql stocks to iex

    let ar = [];
    let accTotal = 0, accDiff = 0;
    
    for(const stocks in orgStock) {
      let st = stocks;
      let s = orgStock[stocks];
      //console.log("stocks: " , s);
      
      let orgPrice = s.price || 0, qty = s.qty || 0,
      status = s.status || 0, company = s.company; 

      let curPrice, curChange, curPercent, curUpdate;
      let avPrice, avVol;

      curPrice = s.latestPrice || 0;
      curChange = s.change || 0;
      curPercent = s.changePercent;
      curUpdate = s.latestUpdate;

      avVol = s.avgTotalVolume;
      avPrice = s.moving || 0;

      //console.log("st: " + st + ", change: " + curChange + ", curPrice: " + curPrice + ", avPrice: " + avPrice);

      if(status == 'active') {
        // We always show the total and diff for the active
        // stocks. All the others (watch, sold) are not counted.
        
        accTotal += curPrice * qty;
        accDiff += curChange * qty;
      }

      // Create value from qty times price
      let value = formatMoney.format(qty * orgPrice);

      // Create orgPer from (price - orgPrice)/orgPrice

      let orgPer = formatMoney.format((curPrice - orgPrice) / orgPrice);

      // If orgPer is neg make it red. 

      if(orgPer.indexOf('-') !== -1) {
        orgPer = `<span class='neg'>${orgPer}</span>`;
      }

      // Take data[0] appart into price, qty, avPrice, change, percent,
      // avgVolume, moving
  
      let price = formatMoney.format(curPrice);

      let change = formatMoney.format(curChange);

      let percent = formatPercent.format(curPercent == null ? 0 : curPercent);

      orgPrice = formatMoney.format(parseFloat((orgPrice == null ? 0 : orgPrice), 10));
      
      qty = qty.toLocaleString();

      company = company.toLowerCase();
      
      avVol = (avVol == null ? 0 : avVol).toLocaleString();

      let movingPer = formatPercent.format((curPrice - avPrice) /avPrice);

      avPrice = formatMoney.format(avPrice);

      // If Change/% Change is negitive make it red

      if(change.indexOf('-') !== -1) {
        change = `<span class='neg'>${change}</span>`;
        percent = `<span class='neg'>${percent}</span>`;
      }

      // If movingPer is neg make it red.

      if(movingPer.indexOf('-') !== -1) {
        movingPer = `<span class='neg'>${movingPer}</span>`;
      }
    
      // Make the row
    
      str += `<tr><td><span>${st}</span><br><span>${company}</span></td><td>${price}</td>
<td>${avPrice}<br>${movingPer}</td><td>${orgPrice}<br>${orgPer}</td>
<td>${qty}<br>${value}</td><td>${avVol}</td><td>${change}<br>${percent}</td><td>${status}</td></tr>`;
    }

    //console.log("mutuals: ", data.mutuals);
    
    str += `
    </tbody
    </table>
    <table id='mutuals' border='1'>
    <thead>
    <tr><th>Name</th><th>Price</th><th>Value</th><th>Qty</th><th>Date</th></tr>
    </thead>
    <tbody>
`;

    let mutTotal = 0.0;

    for(const k in data.mutuals) {
      let mut = data.mutuals[k];
      str += "<tr><td>" + k + "</td>";
      mutTotal += mut[1];
      let mprice = formatMoney.format(mut[0]);
      let value = formatMoney.format(mut[1]);
      let qty = formatNumber.format(mut[2]);
      let date = mut[3];
      str += "<td>" + mprice + "</td><td>" + value + "</td><td>" + qty + "</td><td>" + date + "</td><tr>\n";
    }

    str += "</tbody>\n</table>";

    let grandTotal = mutTotal + accTotal;
    
    // Now Make totals row

    str += `
    </tbody>
    </table>
    <table id='totals'>
      <tr><th>Diff:</th><th>${formatMoney.format(accDiff)}</th></tr>
      <tr><th>Stocks Total:</th><th>${formatMoney.format(accTotal)}</th></tr>
      <tr><th>Mutual Total:</th><th>${formatMoney.format(mutTotal)}</th></tr>
      <tr><th>Total:</th><th>${formatMoney.format(grandTotal)}</th></tr>        
    </table>
`;

    // Render the data to 'stock-data'. This will remove the 'wait' logo

    $("#stock-data").html(str);

    $("#attribution").html('Data provided by <a href="https://iexcloud.io">IEX Cloud</a> ' +
                           'and <a href="https://alphavantage.co">Alpha Vantage</a>');

    // If we Click on the first field which is the stock name. This takes you
    // to marketwatch.com
  
    $("body").on("click", "#stocks td:first-child", function(e) {
      // the td is stock and company each in a span. The stock is the
      // first span.
      var stk = $('span:first-child', this).text();
      var url = "https://www.marketwatch.com/investing/stock/"+stk;
      var w1 = window.open(url, '_blank');
      return false;
    });

    var status = $("#selectstatus select").val();

    // This is the value from the <select><option>
    // It could be ALL, active, watch or sold.
    
    //console.log("New status:", status);

    // Hide All rows then look at status to tell what to do.

    $("#stocks tbody tr").hide();

    $("#stocks tbody tr").each(function() {
      let stat = $("td:last-child", this).text(); // active, mutual, watch or sold

      // If the status is active and the last column says mutual show
      // the row.
      
      if(status == 'active' && stat == 'mutual') {
        $(this).closest('tr').show();
      } else {
        // If the status equals the last column show the row. Here for
        // example status could be watch and if the last column is
        // watch we will show the row.
        
        if(status == stat) {
          $(this).closest('tr').show();
        }
      }
    });

    // When we change the <select><option>
    
    $("body").on('change', "#selectstatus select", function(e) {
      let sel = $(this).val();
      //console.log("change: "+sel);

      // We change some of the headers depending on the selection
      
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

      // Now do the same thing for the body
      
      if(sel == 'ALL') {
        tr.show();
      } else {
        tr.hide();
        let status = $("#stocks td:last-child"); // status

        // This is similar to the above.
        
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

async function query($page) {
  // Again this is like AJAX.
  // BLP 2021-11-04 -- stock-price-update.php does the IPX logic and
  // uses a secure secret token.

  return await fetch("./stock-price-update.php", {
    body: "page=" + $page, // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json());
};

// stock-price-update.js
// This is used by stock-price-update.php
// This gets 'message's from stock-price-update-worker.js
// BLP 2020-06-04 -- changed the php and worker files to use ipx to get
// 200dayMovingAvg and avgTotalVolume. No changes were required to this
// program
// BLP 2020-10-21 -- include mutual funds in the active items.

'use strict';

// w1 is the new Worker. It does most of the real work.

var w1 = new Worker("stock-price-update-worker.js");

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

// Listen for a message from the Worker

w1.addEventListener("message", function(evt) {
  // The data from the worker is returned as an array
  // data[0] is an object with the stock info.
  // data[1]-[4] are the dow jones info.
  
  let data = JSON.parse(evt.data),
  djiAv = data[1],
  djiChange = data[2],
  djiPercent = data[3],
  djiDate = data[4];

  //console.log("DJI Date: " + djiDate);

  let str = `
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

  let accTotal = 0, accDiff = 0;

  // Take data[0] appart into price, qty, avPrice, change, percent,
  // volume, avgVolume, moving
  
  for(let [k, v] of Object.entries(data[0])) {
    v.price = v.price == null ? 0 : v.price;
    v.qty = v.qty == null ? 0 : v.qty;
    v.avPrice = v.avPrice == null ? 0 : v.avPrice;
    v.change = v.change == null ? 0 : v.change;
    
    if(v.status == 'active' || v.status == 'mutual') {
      //console.log("stock: %s, price: %f, qty: %d", k, v.price, parseInt(v.qty));
      accTotal += v.price * v.qty;
      accDiff += v.change * v.qty;
    }

    let moving = (v.price - v.avPrice) / v.avPrice;

    let price = v.price
                .toLocaleString(undefined, {style: 'currency',
                currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2}),
    vol = (v.vol == null ? 0 : v.vol).toLocaleString(),
    change = v.change
             .toLocaleString(undefined, {style: 'currency', currency: 'USD',
             minimumFractionDigits: 2, maximumFractionDigits: 2}),
    percent = (v.chper == null ? 0 : v.chper)
              .toLocaleString(undefined, {style: 'percent',
              minimumFractionDigits: 2, maximumFractionDigits: 2}),
    orgPrice = parseFloat((v.orgPrice == null ? 0 : v.orgPrice), 10)
               .toLocaleString(undefined, {style: 'currency', currency: 'USD',
               minimumFractionDigits: 2, maximumFractionDigits: 2}),
    qty = v.qty.toLocaleString(),
    status = v.status,
    company = v.company.toLowerCase(),
    avVol = (v.avVol == null ? 0 : v.avVol).toLocaleString(),
    avPrice = v.avPrice.toLocaleString(undefined, {style: 'currency',
              currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2}),
    movingPer = moving.toLocaleString(undefined, {style: 'percent',
              minimumFractionDigits: 2, maximumFractionDigits: 2}),
    // Create value from qty times price
    value = (v.qty * v.price).toLocaleString(undefined, {style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2, maximumFractionDigits: 2}),
    // Create orgPer from (price - orgPrice)/orgPrice
    orgPer = ((v.price - v.orgPrice) / v.orgPrice)
             .toLocaleString(undefined, {style: 'percent',
             minimumFractionDights: 2, maximumFractionDigits: 2});

    // If orgPer is neg make it red. 
    
    if(orgPer.indexOf('-') !== -1) {
      orgPer = `<span class='neg'>${orgPer}</span>`;
    }

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
    
    if(new Date(v.last).getDay() == new Date().getDay()) {
      vol = '<span class="current">' + vol + '</span>';
    }

    // Make the row
    
    str += `<tr><td><span>${k}</span><br><span>${company}</span></td><td>${price}</td>
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

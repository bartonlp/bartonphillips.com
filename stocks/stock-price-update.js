// stock-price-3.js
// This is used by stock-price-3.php
// This will be a  'worker'

var w1 = new Worker("stock-price-update-worker.js");
let noper = "<span class='noper'>%</span>";

w1.addEventListener("message", function(evt) {
  let data = JSON.parse(evt.data);

  djiAv = parseFloat(data[1], 10);
  djiChange = parseFloat(data[2], 10);
  djiPercent = parseFloat(data[3], 10) / 100;
  djiDate = data[4];

  if(djiChange < 0) {
    $(".posNeg").css('color', 'red');
  } else {
    $(".posNeg").css('color', 'black');
  }
  
  let str = `
<h2><span class='small'>As of ${djiDate}<br></span>
Dow Jones Average:
${djiAv.toLocaleString(undefined, {style: 'currency', currency: 'USD', 
  minimumFractionDigits: 2, maximumFractionDigits: 2})},

Change: <span class='posNeg'>
${djiChange.toLocaleString(undefined, {
  style: 'currency', currency: 'USD',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>,

<span class='posNeg'>
${djiPercent.toLocaleString(undefined, {
  style: 'percent',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></h2>

<h4>${new Date}</h4>            
<table border="1" id="stocks">
<thead>
<tr><th>Stock</th><th>Price</th><th>Av Price</th><th>Buy Price<br>% Diff</th><th>Qty</th>
<th>Volume</th><th>Av Vol</th><th>Change<br>% Change</th><th>Status</th></tr>
</thead>
<tbody>
`;

  let accTotal = 0, accDiff = 0;
  
  for(let [k, v] of Object.entries(data[0])) {
    if(v.status == 'active') {
      accTotal += v.price * v.qty;
      accDiff += v.change * v.qty;
    }

    let moving = (v.price - v.avPrice) / v.avPrice;
    
    let price = v.price
                .toLocaleString(undefined, {style: 'currency',
                currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2}),
    vol = v.vol.toLocaleString(),
    change = v.change
             .toLocaleString(undefined, {style: 'currency', currency: 'USD',
             minimumFractionDigits: 2, maximumFractionDigits: 2}),
    percent = v.chper
              .toLocaleString(undefined, {style: 'percent',
              minimumFractionDigits: 2, maximumFractionDigits: 2}),
    orgPrice = parseFloat(v.orgPrice, 10)
               .toLocaleString(undefined, {style: 'currency', currency: 'USD',
               minimumFractionDigits: 2, maximumFractionDigits: 2}),
    qty = v.qty.toLocaleString(),
    status = v.status,
    company = v.company.toLowerCase(),
    avVol = v.avVol.toLocaleString(),
    avPrice = v.avPrice.toLocaleString(undefined, {style: 'currency',
              currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2}),
    movingPer = moving
                .toLocaleString(undefined, {style: 'percent',
                minimumFractionDigits: 2, maximumFractionDigits: 2}),
    orgPer = ((v.price - v.orgPrice) / v.orgPrice)
             .toLocaleString(undefined, {style: 'percent',
             minimumFractionDights: 2, maximumFractionDigits: 2});

    // If orgPer is neg make it red. It is in 'Buy Price/% Diff'
    
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
    
    str += `<tr><td><span>${k}</span><br><span>${company}</span></td><td>${price}</td>
<td>${avPrice}${noper}<br>${movingPer}</td><td>${orgPrice}${noper}<br>${orgPer}</td><td>${qty}</td>
<td>${vol}</td><td>${avVol}</td><td>${change}${noper}<br>${percent}</td><td>${status}</td></tr>`;
  }
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

  $("#stock-data").html(str);

  // Add listener for the stock td.
  
  $("body").on("click", "#stocks td:first-child", function(e) {
    // the td is stock and company each in a span. The stock is the
    // first span.
    var stk = $('span:first-child', this).text().replace(/-BLP/, '');

    var url = "https://www.marketwatch.com/investing/stock/"+stk;
    var w1 = window.open(url, '_blank');
    return false;
  });

  // Put select into div
  
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

  // Hide All rows then look at status to tell what to do.
  
  $("#stocks tbody tr").each(function() {
    $(this).hide();
    let status = $("td:last-child", this).text();
    switch(status) {
      case 'watch': // if watch remove bye price and qty
        $("td:nth-child(4), td:nth-child(5)", this).text("");
        break;
      case 'active': // if active show the row
        $(this).closest('tr').show();
        break;
    }
  });

  $("body").on('change', "#selectstatus select", function(e) {
    let sel = $(this).val();
    switch(sel) {
      case 'sold':
        $("#stocks thead th:nth-child(4)").html("Sell Price");
      case 'active':
      case 'ALL':
        $("#stocks td:nth-child(4), #stocks td:nth-child(5), "+
            "#stocks th:nth-child(4), #stocks th:nth-child(5)").show();
        if(sel != 'sold') {
          $("#stocks thead th:nth-child(4)").html("Buy Price<br>% Diff");
        }
        break;
      case 'watch':
        $("#stocks td:nth-child(4), #stocks td:nth-child(5), "+
            "#stocks th:nth-child(4), #stocks th:nth-child(5)").hide();
        break;
    }      

    let tr = $("#stocks tbody tr");
    if(sel == 'ALL') {
      tr.show();
    } else {
      tr.hide();
      let status = $("#stocks td:last-child"); // status

      status.each(function() {
        if(sel == $(this).text()) {
          $(this).closest('tr').show();
        }
      });
    }
  });
});

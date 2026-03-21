// stockprice.js
// This is used by stockprice.php
/*
CREATE TABLE `stocks` (
`stock` varchar(10) NOT NULL,
`price` decimal(8,2) DEFAULT NULL,
`qty` int DEFAULT NULL,
`name` varchar(255) DEFAULT NULL,
`lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`status` enum('active','watch','sold','mutual','IRA') DEFAULT NULL,
PRIMARY KEY (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/

'use strict';

const formatNumber = new Intl.NumberFormat(undefined, {
  // These options are needed to round to whole numbers if that's what you want.
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});
  
const formatMoney = new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'USD',

  // These options are needed to round to whole numbers if that's what you want.
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});

const formatPercent = new Intl.NumberFormat(undefined, {
  style: 'percent',
  minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
  maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});

// Put the select text into the 'selectstatus' div

const msg = `
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

Date.prototype.stdTimezoneOffset = function (): number {
  var jan = new Date(this.getFullYear(), 0, 1);
  var jul = new Date(this.getFullYear(), 6, 1);
  return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
}

Date.prototype.dst = function(): boolean {
  return this.getTimezoneOffset() < this.stdTimezoneOffset();
}
/*
interface StockApiResponse {
  grandTotal: number;
  stock: {
    [symbol: string]: StockInfo;
  };
}

interface StockInfo {
  price: number;
  change: number;
  percentChange: string;
  high: number;
  low: number;
  previousClose: number;
  time: string;
  companyName?: string;
  dividend?: number;
  divYield?: string;
  moving50?: number;
  moving200?: number;
  divDate?: string;
  divExDate?: string;
  qty: number;
  purchasePrice: number;
  name?: string;
  status: string;
}
*/

function gotoDJIA() {
  const url = "https://www.marketwatch.com/investing/index/djia";
  window.open(url, '_blank');
  return false;
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

  if(date.dst()) { // BLP 2018-03-15 -- if dst add 1 hour so 14 and 21 UTC are still correct.
    ++time;
  }

  // Check the time and the day of week. We only want to do this from
  // 9am to 4pm. Note time is UTC or Grenich time

  if(start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7))) return;

  //console.log("start:", start, ", time:", time, " day: ", day, ", date:", date);

  query('web').then(data => {
    if(!data) {
      console.error("No data returned");
      return;
    }

    data = JSON.parse(data);
    
    let parsed = data;

    const dji = parsed.dji;
    console.log("dji=", dji);

    $("#DJI").html(`${dji.price}, high: ${dji.high}, low: ${dji.low}`);
    
    const str = `<table border="1" id="stocks">
<thead>
<tr><th>Stock</th><th>Price</th><th>Buy Price<br>% Diff</th><th class='qtyval'>Qty<br>Value</th><th>Change<br>% Change</th>
<th>Div per Share<br>Div Value</th><th>50 Moving<br>200 Moving</th></tr>
</thead>
<tbody>
${parsed.rows}
</tbody>
${parsed.footer}
</table>
`;

    // Render the data to 'stock-data'. This will remove the 'wait' logo

    $("#stock-data").html(str);
  });

  $("#attribution").html('Data provided by Alpha Advantage</a>');

  // If we Click on the first field which is the stock name. This takes you
  // to marketwatch.com

  $("body").on("click", ".stocksymbol", function(e) {
    const stk = $(this).text();
    const url = "https://www.marketwatch.com/investing/stock/"+stk;
    window.open(url, '_blank');
    return false;
  });

  // Initial select
  
  const sel = $("#selectstatus select").val();
  doSelect(typeof sel === "string" ? sel : "ALL");

  function doSelect(status: string): void {
    if(status == "ALL") {
      $("#stocks tbody tr").show();
    } else {
      // Hide all first
      $("#stocks tbody tr").hide();

      if(status == "active" || status == "mutual") {
        $(".active, .mutual").show();
      } else if(status == 'sold') {
        $(".sold").show();
      } else if(status == 'watch') {
        $(".watch").show();
      }
    }
  }
  
  // When we change the <select><option>
    
  $("body").on('change', "#selectstatus select", function(e) {
    let sel = $(this).val();

    // We change some of the headers depending on the selection

    switch(sel) {
      case 'sold':
        $("#stocks .gtyval").html("Sell Price<br>% Diff");
        break;
      case 'watch':
        $("#stocks .qtyval").html("Watch Price<br>% Diff");
        break;
      case 'active':
      case 'ALL':
        $("#stocks .qtyval").html("Qty<br>Value");
        break;
    }      

    doSelect(sel);
  });
};

async function query(page: string): Promise<string | undefined> {
  // Again this is like AJAX.

  try {
  return await fetch("./stockprice.php", {
    body: "page=" + page, // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => {
    //return data.json();
    return data.text();
  });
  } catch(err) {
    console.log("Error: ", err);
    return undefined;
  }
};

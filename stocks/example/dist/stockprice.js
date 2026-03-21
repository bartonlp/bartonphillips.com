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
let str;
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
Date.prototype.stdTimezoneOffset = function () {
    var jan = new Date(this.getFullYear(), 0, 1);
    var jul = new Date(this.getFullYear(), 6, 1);
    return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
};
Date.prototype.dst = function () {
    return this.getTimezoneOffset() < this.stdTimezoneOffset();
};
setInterval(getInfo, 300000); // Five Min. NOTE getinfo(start) is false if not present which is the default.
getInfo(true); // pass true as start.
// start will be true the first time from the above call, but will be
// false from every call by setInterval().
function getInfo(start = false) {
    // Get today's date
    let date = new Date();
    // Get time and day
    let time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
    let day = date.getDay(); // we only take readings during the week not Sat. or Sun.
    if (date.dst()) { // BLP 2018-03-15 -- if dst add 1 hour so 14 and 21 UTC are still correct.
        ++time;
    }
    // Check the time and the day of week. We only want to do this from
    // 9am to 4pm. Note time is UTC or Grenich time
    if (start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7)))
        return;
    //console.log("start:", start, ", time:", time, " day: ", day, ", date:", date);
    query('web').then(data => {
        if (!data) {
            console.error("No data returned");
            return;
        }
        console.log("data:", data);
        let parsed;
        try {
            parsed = JSON.parse(data);
        }
        catch (err) {
            console.error("JSON parse failed", err);
            return;
        }
        const gt = parsed.grandTotal;
        const grandTotal = formatMoney.format(parsed.grandTotal);
        const stockEntries = Object.entries(parsed.stock);
        str = `<table border="1" id="stocks">
<thead>
<tr><th>Stock</th><th>Price</th><th>Buy Price<br>% Diff</th><th>Qty<br>Value</th><th>Change<br>% Change</th>
<th>Div per Share<br>Div Value</th><th>50 Moving<br>200 Moving</th></tr>
</thead>
<tbody>
`;
        let ar = [], byeValue = 0, orgValue = 0, curPrice, curChange, curPercent, curHigh, curLow, curUpdate, qty, byePrice, company, status, companyName, div, divYield, moving50, moving200, prevClose, divDate, divExDate, divValue, divTotal = 0;
        for (const [symbol, q] of stockEntries) {
            const info = q;
            curPrice = info.c;
            curChange = info.d;
            curPercent = info.pd;
            curHigh = info.h;
            curLow = info.l;
            prevClose = info.pc;
            curUpdate = info.t;
            companyName = info.companyName;
            div = info.dividend;
            divYield = info.divYield;
            moving50 = info.moving50;
            moving200 = info.moving200;
            divDate = info.divDate;
            divExDate = info.divExDate;
            qty = info.qty;
            byePrice = info.purchasePrice;
            company = info.name;
            status = info.status;
            if (symbol == 'DIA') {
                curPrice *= 100;
                curChange *= 100;
                curHigh *= 100;
                curLow *= 100;
                let p = formatMoney.format(curPrice), high = formatMoney.format(curHigh), low = formatMoney.format(curLow), change = formatMoney.format(curChange), percent = curPercent;
                if (change.indexOf('-') !== -1) {
                    change = `<span class='neg'>${change}</span>`;
                    percent = `<span class='neg'>${percent}</span>`;
                }
                $("#DJI").html(`${p}, high ${high}, low ${low}, change ${change}, percent ${percent}`);
                continue;
            }
            orgValue += byePrice * qty;
            // Create value from qty times price
            let value = formatMoney.format(qty * curPrice);
            // Create orgPer from (price - byePrice)/byePrice
            let orgPer = formatPercent.format((curPrice - byePrice) / byePrice);
            // If orgPer is neg make it red. 
            if (orgPer.indexOf('-') !== -1) {
                orgPer = `<span class='neg'>${orgPer}</span>`;
            }
            let price = formatMoney.format(curPrice);
            let change = formatMoney.format(curChange);
            let percent = curPercent;
            if (change.indexOf('-') !== -1) {
                change = `<span class='neg'>${change}</span>`;
                percent = `<span class='neg'>${percent}</span>`;
            }
            divValue = '';
            let tmp = 0;
            if (typeof div === 'number' && typeof qty === 'number') {
                tmp = div * qty;
                divValue = tmp;
                divTotal += tmp;
            }
            if (!Number.isNaN(tmp)) {
                switch (symbol) {
                    case 'CII':
                        tmp = 857.13;
                        break;
                    case 'RDIV':
                        tmp = 981.50;
                        break;
                    case 'MBGAF':
                        tmp = 1462.59;
                        break;
                }
                divTotal += tmp;
                //console.log("divTotal=", divTotal);
                divValue = !Number.isNaN(tmp) ? formatMoney.format(tmp) : '';
            }
            else {
                console.log(`stock=${symbol}, div=${div}, qty=${qty}, val=${tmp}`);
            }
            moving50 = moving50 ? formatMoney.format(moving50) : '';
            moving200 = moving200 ? formatMoney.format(moving200) : '';
            byePrice = byePrice ? formatMoney.format(byePrice) : '';
            curHigh = curHigh ? formatMoney.format(curHigh) : '';
            curLow = curLow ? formatMoney.format(curLow) : '';
            prevClose = prevClose ? formatMoney.format(prevClose) : '';
            company = companyName ? companyName : company;
            div = div ?? '';
            // Make the row
            qty = qty.toLocaleString();
            str += `<tr class='${status}'><td><span>${symbol}</span><br><span>${company}</span></td>
             <td>${price}<br>h: ${curHigh}<br>l: ${curLow}</td>
             <td>${byePrice}<br>${orgPer}</td><td>${qty}<br>${value}</td><td>${change}<br>${percent}</td>
             <td>${div}<br>${divValue}</td><td>${moving50}<br>${moving200}</td></tr>`;
        }
        str += "</tbody>\n";
        const byeOrg = formatMoney.format(orgValue);
        const byeOrgDiff = formatPercent.format((gt - orgValue) / orgValue);
        const total = formatMoney.format(divTotal);
        str += `<tfoot>\n<tr><th>Totals</th><th>${grandTotal}</th><th>${byeOrg}<br>${byeOrgDiff}</th>
           <th colspan='2'></th><th>${total}</th><th></th>\n</tfoot>\n</table>`;
        // Now Make totals row
        str += `
    </tbody>
    </table>
`;
        // Render the data to 'stock-data'. This will remove the 'wait' logo
        $("#stock-data").html(str);
    });
    $("#attribution").html('Data provided by Alpha Advantage</a>');
    // If we Click on the first field which is the stock name. This takes you
    // to marketwatch.com
    $("body").on("click", "#stocks td:first-child, #mutuals td:first-child", function (e) {
        // the td is stock and company each in a span. The stock is the
        // first span.
        var stk = $('span:first-child', this).text();
        var url = "https://www.marketwatch.com/investing/stock/" + stk;
        var w1 = window.open(url, '_blank');
        return false;
    });
    // Initial select
    const sel = $("#selectstatus select").val();
    doSelect(typeof sel === "string" ? sel : "ALL");
    function doSelect(status) {
        if (status == "ALL") {
            $("#stocks tbody tr").show();
        }
        else {
            // Hide all first
            $("#stocks tbody tr").hide();
            // Now look at each tr
            $("#stocks tbody tr").each(function () {
                // If active or mutual show
                switch (status) {
                    case 'active':
                        if ($(this).hasClass('active') || $(this).hasClass('mutual')) {
                            $(this).show();
                        }
                        break;
                    case 'sold':
                        if ($(this).hasClass('sold')) {
                            $(this).show();
                        }
                        break;
                    case 'watch':
                        if ($(this).hasClass('watch')) {
                            $(this).show();
                        }
                        break;
                    default:
                        console.log("NO Class,", $(this).attr('class'));
                }
            });
        }
    }
    // When we change the <select><option>
    $("body").on('change', "#selectstatus select", function (e) {
        let sel = $(this).val();
        // We change some of the headers depending on the selection
        switch (sel) {
            case 'sold':
                $("#stocks thead th:nth-child(4)").html("Sell Price<br>% Diff");
                break;
            case 'watch':
                $("#stocks thead th:nth-child(4)").html("Watch Price<br>% Diff");
                break;
            case 'active':
            case 'ALL':
                $("#stocks thead th:nth-child(4)").html("Qty<br>Value");
                break;
        }
        doSelect(sel);
    });
}
;
async function query(page) {
    // Again this is like AJAX.
    // BLP 2021-11-04 -- stockprice.php does the IPX logic and
    // uses a secure secret token.
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
    }
    catch (err) {
        console.log("Error: ", err);
        return undefined;
    }
}
;
//# sourceMappingURL=stockprice.js.map
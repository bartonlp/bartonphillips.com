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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
var str;
var formatNumber = new Intl.NumberFormat(undefined, {
    // These options are needed to round to whole numbers if that's what you want.
    minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
    maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});
var formatMoney = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
    // These options are needed to round to whole numbers if that's what you want.
    minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
    maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});
var formatPercent = new Intl.NumberFormat(undefined, {
    style: 'percent',
    minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
    maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
});
// Put the select text into the 'selectstatus' div
var msg = "\n<p>You can select which status to show:\n<select>\n  <option>ALL</option>\n  <option selected>active</option>\n  <option>watch</option>\n  <option>sold</option>\n  </select>\n</p>\n";
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
function getInfo(start) {
    // Get today's date
    if (start === void 0) { start = false; }
    var date = new Date();
    // Get time and day
    var time = date.getUTCHours(); // we only take reading from 9am to 4pm EST
    var day = date.getDay(); // we only take readings during the week not Sat. or Sun.
    if (date.dst()) { // BLP 2018-03-15 -- if dst add 1 hour so 14 and 21 UTC are still correct.
        ++time;
    }
    // Check the time and the day of week. We only want to do this from
    // 9am to 4pm. Note time is UTC or Grenich time
    if (start !== true && ((time > 21 || time < 14) || (day == 6 || day == 7)))
        return;
    //console.log("start:", start, ", time:", time, " day: ", day, ", date:", date);
    query('web').then(function (data) {
        console.log("data:", data);
        data = JSON.parse(data);
        var gt = data.grandTotal;
        var grandTotal = formatMoney.format(gt);
        str = "<table border=\"1\" id=\"stocks\">\n<thead>\n<tr><th>Stock</th><th>Price</th><th>Buy Price<br>% Diff</th><th>Qty<br>Value</th><th>Change<br>% Change</th>\n<th>Div per Share<br>Div Value</th><th>50 Moving<br>200 Moving</th></tr>\n</thead>\n<tbody>\n";
        var ar = [], byeValue = 0, orgValue = 0, curPrice, curChange, curPercent, curHigh, curLow, curUpdate, qty, byePrice, company, status, companyName, div, divYield, moving50, moving200, prevClose, divDate, divExDate, divValue, divTotal = 0;
        for (var _i = 0, _a = Object.entries(data.stock); _i < _a.length; _i++) {
            var _b = _a[_i], symbol = _b[0], q = _b[1];
            console.log(symbol + ", q=", q);
            curPrice = q.c;
            curChange = q.d;
            curPercent = q.pd;
            curHigh = q.h;
            curLow = q.l;
            prevClose = q.pc;
            curUpdate = q.t;
            companyName = q.companyName;
            div = q.dividend;
            divYield = q.divYield;
            moving50 = q.moving50;
            moving200 = q.moving200;
            divDate = q.divDate;
            divExDate = q.divExDate;
            qty = q.qty;
            byePrice = q.purchasePrice;
            company = q.name;
            status = q.status;
            if (symbol == 'DIA') {
                curPrice *= 100;
                curChange *= 100;
                curHigh *= 100;
                curLow *= 100;
                var p = formatMoney.format(curPrice), high = formatMoney.format(curHigh), low = formatMoney.format(curLow), change_1 = formatMoney.format(curChange), percent_1 = curPercent;
                if (change_1.indexOf('-') !== -1) {
                    change_1 = "<span class='neg'>".concat(change_1, "</span>");
                    percent_1 = "<span class='neg'>".concat(percent_1, "</span>");
                }
                $("#DJI").html("".concat(p, ", high ").concat(high, ", low ").concat(low, ", change ").concat(change_1, ", percent ").concat(percent_1));
                continue;
            }
            orgValue += byePrice * qty;
            // Create value from qty times price
            var value = formatMoney.format(qty * curPrice);
            // Create orgPer from (price - byePrice)/byePrice
            var orgPer = formatPercent.format((curPrice - byePrice) / byePrice);
            // If orgPer is neg make it red. 
            if (orgPer.indexOf('-') !== -1) {
                orgPer = "<span class='neg'>".concat(orgPer, "</span>");
            }
            var price = formatMoney.format(curPrice);
            var change = formatMoney.format(curChange);
            var percent = curPercent;
            if (change.indexOf('-') !== -1) {
                change = "<span class='neg'>".concat(change, "</span>");
                percent = "<span class='neg'>".concat(percent, "</span>");
            }
            divValue = '';
            var tmp = div * qty;
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
                console.log("stock=".concat(symbol, ", div=").concat(div, ", qty=").concat(qty, ", val=").concat(tmp));
            }
            moving50 = moving50 ? formatMoney.format(moving50) : '';
            moving200 = moving200 ? formatMoney.format(moving200) : '';
            byePrice = byePrice ? formatMoney.format(byePrice) : '';
            curHigh = curHigh ? formatMoney.format(curHigh) : '';
            curLow = curLow ? formatMoney.format(curLow) : '';
            prevClose = prevClose ? formatMoney.format(prevClose) : '';
            company = companyName ? companyName : company;
            div = div !== null && div !== void 0 ? div : '';
            // Make the row
            qty = qty.toLocaleString();
            str += "<tr class='".concat(status, "'><td><span>").concat(symbol, "</span><br><span>").concat(company, "</span></td>\n             <td>").concat(price, "<br>h: ").concat(curHigh, "<br>l: ").concat(curLow, "</td>\n             <td>").concat(byePrice, "<br>").concat(orgPer, "</td><td>").concat(qty, "<br>").concat(value, "</td><td>").concat(change, "<br>").concat(percent, "</td>\n             <td>").concat(div, "<br>").concat(divValue, "</td><td>").concat(moving50, "<br>").concat(moving200, "</td></tr>");
        }
        str += "</tbody>\n";
        var byeOrg = formatMoney.format(orgValue);
        var byeOrgDiff = formatPercent.format((gt - orgValue) / orgValue);
        divTotal = formatMoney.format(divTotal);
        str += "<tfoot>\n<tr><th>Totals</th><th>".concat(grandTotal, "</th><th>").concat(byeOrg, "<br>").concat(byeOrgDiff, "</th>\n           <th colspan='2'></th><th>").concat(divTotal, "</th><th></th>\n</tfoot>\n</table>");
        // Now Make totals row
        str += "\n    </tbody>\n    </table>\n";
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
    doSelect($("#selectstatus select").val());
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
        var sel = $(this).val();
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
                $("#stocks thead th:nth-child(4)").html("Buy Price<br>% Diff");
                break;
        }
        doSelect(sel);
    });
}
;
function query($page) {
    return __awaiter(this, void 0, void 0, function () {
        var err_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, , 3]);
                    return [4 /*yield*/, fetch("./stockprice.php", {
                            body: "page=" + $page, // make this look like form data
                            method: 'POST',
                            headers: {
                                'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
                            }
                        }).then(function (data) {
                            //return data.json();
                            return data.text();
                        })];
                case 1: return [2 /*return*/, _a.sent()];
                case 2:
                    err_1 = _a.sent();
                    console.log("Error: ", err_1);
                    return [3 /*break*/, 3];
                case 3: return [2 /*return*/];
            }
        });
    });
}
;

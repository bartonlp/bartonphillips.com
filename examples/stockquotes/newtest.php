<?php
$_site = require_once(getenv("SITELOADNAME"));

use PHPHtmlParser\Dom;

if($_POST['page'] == 'web') {
  $S = new Database($_site);
  //$sql = $_POST['sql'];

  $sql = "select stock, price, qty, status, name from stocks.stocks ".
         "where stock not in('DJI','ZENO') and status != 'mutual'";
  
  $S->query($sql);
  $ar = [];
  $r = $S->getResult();

  while(list($stock, $price, $qty, $status, $company) = $S->fetchrow($r, 'num')) {
    $stk = preg_replace("/-BLP/", '', $stock);

    $sql = "select volume, price from stocks.pricedata where stock='$stk' order by date desc limit 100";
    $S->query($sql);

    for($cnt=0, $avVol=0, $avPrice=0; list($volume, $p) = $S->fetchrow('num'); ++$cnt) {
      $avVol += $volume;
      $avPrice += $p;
    }
    $avVol = round($avVol / $cnt);
    $avPrice = round($avPrice / $cnt, 2);
    
    $ar[] = [$stock, $price, $qty, $status, $company, $avVol, $avPrice];
  }

  // use Dom to scrape the wsj site of the DJIA info.
  
  $dom = new Dom;
  $dom->loadFromUrl('http://quotes.wsj.com/index/DJIA');
  $dji = $dom->find("#quote_val")->text;
  $change = $dom->find("#quote_change")->text;
  $changePercent = $dom->find("#quote_changePer")->text;
  $quoteDate = $dom->find("#quote_dateTime")->text;
  
  $ret = json_encode(array('stocks'=>$ar,
                           'dji'=>$dji,
                           'change'=>$change,
                           'per'=>$changePercent,
                           'date'=>$quoteDate
                          )
                    );
  echo $ret;
  exit();
}

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
</head>
<body>
<div id="stock-data"></div>

<script>
function query() {
  return fetch("./newtest.php", {
    body: "page=web", // make this look like form data
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded' // We need the x-www-form-urlencoded type
    }
  }).then(data => data.json()); // 'stock-price-3.php' POST returns json data.
};

query().then(data => {
    let dji = data.dji,
    djichange = data.change,
    djipercent = data.per,
    djidate = data.date;

    // I want orgStock to be a new variable is not changed when I do
    // the below map.
    
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

    return fetch(url)
        .then(data => data.text())
        .then(data => {
      let ar = {};
      let d = JSON.parse(data);

      for(let stocks of orgStock) {
        let t, st = stocks[0], orgPrice = stocks[1] || 0, qty = stocks[2] || 0,
        status = stocks[3] || 0, company = stocks[4], avVol = stocks[5], avPrice = stocks[6];
        let stx = st.replace(/-BLP/, '').replace(/RDS-A/, 'RDS.A');
        t = d[stx].quote;
        ar[st] = {
          orgPrice: orgPrice, qty: qty, status: status, company: company, avVol: avVol,
          avPrice: avPrice, price: t.latestPrice, vol: t.latestVolume, change: t.change,
          chper: t.changePercent
        };
      }
      var dataStr = JSON.stringify([ar, dji, djichange, djipercent, djidate]);
      return dataStr;
      //postMessage(dataStr);
    });
  }).then(d => {
  let data = JSON.parse(d);
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
\${djiAv.toLocaleString(undefined, {style: 'currency', currency: 'USD', 
  minimumFractionDigits: 2, maximumFractionDigits: 2})},

Change: <span class='posNeg'>
\${djiChange.toLocaleString(undefined, {
  style: 'currency', currency: 'USD',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>,

<span class='posNeg'>
\${djiPercent.toLocaleString(undefined, {
  style: 'percent',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></h2>

<h4>\${new Date}</h4>            
<table border="1" id="stocks">
<thead>
<tr><th>Stock</th><th>Price</th><th>Av Price</th><th>Buy Price<br>% Diff</th><th>Qty</th>
<th>Volume</th><th>Av Vol</th><th>Change<br>% Change</th><th>Status</th></tr>
</thead>
<tbody>
`;

  let accTotal = 0, accDiff = 0;
  
  for(let [k, v] of Object.entries(data[0])) {
    accTotal += v.price * v.qty;
    accDiff += v.change * v.qty;

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
    orgPer = ((v.price - v.orgPrice) / v.orgPrice)
             .toLocaleString(undefined, {style: 'percent',
             minimumFractionDights: 2, maximumFractionDigits: 2});

    if(orgPer.indexOf('-') !== -1) {
      orgPer = `<span class='neg'>${orgPer}</span>`;
    }
    
    str += `<tr><td><span>\${k}</span><br><span>\${company}</span></td><td>\${price}</td>
<td>\${avPrice}</td><td>\${orgPrice}<br>\${orgPer}</td><td>\${qty}</td>
<td>\${vol}</td><td>\${avVol}</td><td>\${change}<br>\${percent}</td><td>\${status}</td></tr>`;
  }
  str += `
</tbody>
</table>
<table id='totals'>
<tr><th>Diff:</th><th>\${accDiff.toLocaleString(undefined, {style: 'currency', currency: 'USD',
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</th></tr>
<tr><th>Total:</th><th>\${accTotal.toLocaleString(undefined, {style: 'currency', currency: 'USD', 
  minimumFractionDigits: 2, maximumFractionDigits: 2})}</th></tr>
</table>
`;

  \$("#stock-data").html(str);
});
</script>
</body>
</html>
EOF;

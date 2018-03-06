<?php
// stockanal.php  
// BLP 2018-01-18 -- use ajax to get new table info.
// analyze the stocks in the 'pricedata' table

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setNoEmailErrs(true);
ErrorClass::setDevelopment(true);

function checkUser($S) {
  if($userId = $_COOKIE['SiteId']) {
    $sql = "select name from members where id=$userId";

    if($n = $S->query($sql)) {
      list($memberName) = $S->fetchrow('num');
      if($memberName != "Barton Phillips") {
        echo "<h1>Go Away</h1>";
        exit();
      }
    }
  } else {
    echo "<h1>Go Away</h1>";
    exit();
  }
};

if($_POST['avmove']) {
  $S = new Database($_site);
  checkUser($S);
  
  $move = $_POST['avmove'];

  // Get all of the stocks in my portfolio

  $sql = "select stock, status, price, name from stocks.stocks";
  $S->query($sql);
  $r = $S->getResult(); // Save result

  $an = [];

  // Loop through each stock

  $company;

  while(list($stock, $status, $buyprice, $coname) = $S->fetchrow($r, 'num')) {
    $stock = $stock == "RDS.A" ? $stock = "RDS-A" : $stock;
    $st = preg_replace("/-BLP/", "", $stock);
    $company[$stock] = $coname;

    $sql = "select stock, date, price, volume from stocks.pricedata where stock='$st' ".
           "order by date desc limit $move";

    $S->query($sql);

    // Loop through this stock and save info in $an

    $st = $stock;

    while(list($stock, $date, $price, $volume) = $S->fetchrow('num')) {
      $stock = $stock == "DJI" ? "DJI-AVG" : $stock;

      $an[$st][] = (object)array('price'=>"$price", 'date'=>"$date",
                                 'status'=>$status, 'buyprice'=>$buyprice, 'volume'=>$volume);
    }
  }

  // Add END so we print the last stock
  $an['END'][] = (object)array();

  // Now loop through all of the $an stocks

  $kk = null;
  $moving = '';

  foreach($an as $k=>$v) {
  // If $k != $kk then this is a new stock

    if($k != $kk) {
    // But if $kk is null this is the first time through so don't show the info until next time.

      if($kk) {
      // watch the order of formating. Must do $price after $av
        $av = $price / $c;
        $avVol = $volume == 0 ? 0 : $volume / $c;
        
        $percent = ($lastprice - $av) / $av;
        $perAvVol = $avVol == 0 ? 0 : ($lastvolume - $avVol) / $avVol;
        
        $buypercent = ($lastprice - $buyprice) / $buyprice; 
        $percent = $percent < 0
                   ? "<span class='negchange'>" .number_format($percent * 100, 2)."%</span>"
                   : number_format($percent * 100, 2) . "%";

        $buypercent = $buypercent < 0
                      ? "<span class='negchange'>" .number_format($buypercent * 100, 2)."%</span>"
                      : number_format($buypercent * 100, 2) . "%";

        $perAvVol = $perAvVol < 0
                    ? "<span class='negchange'>" .number_format($perAvVol * 100, 2)."%</span>"
                    : number_format($perAvVol * 100, 2) . "%";

        $av = number_format($av, 2);
        $avVol = number_format($avVol, 0);
        $lastprice = number_format($lastprice, 2);
        $lastvolume = number_format($lastvolume, 0);
        
        $price = number_format($price, 2);

        // make the table rows

        if($status == 'watch') {
          $tr = "<tr class='watch'>";
          $buyprice = $buypercent = '';
        } else {
          $tr = "<tr>";
        }

        $moving .= "$tr<td>".($kk ?: $k)."</td><td>$c</td><td>$av</td>".
                   "<td>$lastprice</td><td>$percent</td><td>$buyprice</td>".
                   "<td>$buypercent</td><td>$lastvolume</td><td>$avVol<br>$perAvVol</td><td>$status</td></tr>";
      }
      // $k != $kk so new stock
      $kk = $k;
      $c = 0;
      $price = $volume = 0;
    }
    // $k == $kk so just accumulate price info

    foreach($v as $vv) {
      if(!count((array)$vv)) {
        break;
      }

      $price += $vv->price;
      $volume += $vv->volume;
      
      if($c == 0) {
        $lastprice = $vv->price;
        $lastvolume = $vv->volume;
      }
      $status = $vv->status;
      $buyprice = $vv->buyprice;
      $c++;
    }
  }
  $ret = json_encode(['moving'=>$moving, 'company'=>$company]);
  echo $ret;
  exit();
}

$S = new $_site->className($_site);
checkUser($S);

$move = 100;

if($_GET['move']) {
  $move = $_GET['move'];
}

$h->title = "Stock Moving Average";
$h->banner = "<h1>Moving Average</h1>";
$h->css =<<<EOF
  <style>
#moving td, #moving th {
  padding: .2rem;
}
#moving td {
  text-align: right;
}
#moving td:nth-child(1) {
  text-align: left;
  cursor: pointer;
  background-color: lightblue;
}
#moving td:nth-child(6), #moving td:nth-child(7) {
  background-color: lightgreen;
  opacity: .7;
}
.negchange {
  color: red;
}
.watch {
  background-color: #FFEFD5;
  opacity: .7;
}
select {
  font-size: .8rem;
  padding: 2px 5px;
}
input[type='submit'] {
  font-size: .8rem;
  padding: 5px;
  border-radius: .2rem;
}
  </style>
EOF;

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  var companyName; // This will be an array from AJAX 'avmove' above.

  let msg = `
<p>You can select which status to show:
<select>
  <option>ALL</option>
  <option selected>active</option>
  <option>watch</option>
  <option>mutual</option>
  <option>sold</option>
</select>
</p>
`;

  $("#selectstatus").html(msg);

  // Hide All rows
  $("table tbody tr").hide();
  // Now make only the 'active' show.
  $("td:last-child").each(function() {
    if('active' == $(this).text()) {
      $(this).closest('tr').show();
    }
  });

  $("body").on('change', "#selectstatus select", function(e) {
    let sel = $(this).val();
    switch(sel) {
      case 'sold': 
        $("table thead th:nth-child(6)").html("Sell Price");
        break;
      case 'mutual':
        $("th:nth-child(8), th:nth-child(9), td:nth-child(8), td:nth-child(9)").hide();
        break;
      default:
        $("table thead th:nth-child(6)").html("Buy Price");
        $("th:nth-child(8), th:nth-child(9), td:nth-child(8), td:nth-child(9)").show();
        break;
    }

    //console.log("sel:", sel);
    let tr = $("#moving tbody tr");
    if(sel == 'ALL') {
      tr.show();
    } else {
      tr.hide();
      let status = $("#moving tbody tr td:last-child"); // status

      status.each(function() {
        if(sel == $(this).text()) {
          $(this).closest('tr').show();
        }
      });
    }
  });

  $("body").on('click', function(e) {
    $("#message").remove();
  });

  // Clicked on the first column 'Symbol'

  $("body").on('contextmenu', "#moving td:first-child", function(e) {
    let stk = $(this).text();

    let pos = $(this).position(),
        width = $(this).innerWidth(),
        xpos = pos.left+width,
        ypos = pos.top,
        company = companyName[stk]; // from AJAX 'avmove'

    $("#message").remove();
    $("<div id='message' style='position: absolute; "+
      "left: "+xpos+"px; top: "+ypos+"px; "+
      "background-color: white; border: 5px solid black;padding: 10px'>"+
      company+"</div>").appendTo($(this));

    e.stopPropagation();
    return false;
  });

  $("body").on('click', "#moving td:first-child", function(e) {
    let stk = $(this).text();
    stk = stk.replace(/-BLP/, '');
    var url = "https://www.marketwatch.com/investing/stock/"+stk; 
    var w1 = window.open(url, '_blank');
    return false;
  });

  $("form").on('click', "input[type='submit']", function(e) {
    let avmove = $("select").val();
    getTable(avmove);
    return false;
  });

  function getTable(avmove) {
    $("h4 span").html(avmove);

    $.ajax({
      url: 'stockanal.php',
      type: 'post',
      data: {avmove: avmove},
      dataType: 'json', // receive json
      success: function(data) {
        //console.log(data);
        companyName = data.company; // data.company is an array of companies indexed by stock
        $("#moving tbody").html(data.moving); // Put the whole table in 'tbody'
        $("#selectstatus select").trigger('change');
      },
      error: function(err) {
        console.log("ERROR:", err);
      }
    });
  };

  getTable(100);
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Render page

echo <<<EOF
$top
<form method="POST">
Select the Average Period
<select name='avmove'>
<option>10</option>
<option>20</option>
<option>30</option>
<option>40</option>
<option>50</option>
<option>60</option>
<option>75</option>
<option>100</option>
<option>150</option>
<option>200</option>
</select>
<br>
<input type='submit'>
</form>
<br>
<h4>Moving Average Period: <span></span></h4>
<div id="selectstatus"></div>
<p>Check <b>Count</b> for the actual number of averaged days.<br>
<b>Left Click</b> on the <i>Stock</i> to goto <i>MarketWatch</i>.<br>
<b>Right Click</b> on <i>Stock</i> symbol to show the company name.</p>
<table id='moving' border="1">
<thead>
<tr><th>Stock</th><th>Count</th><th>Moving</th><th>Last Price</th><th>Change %</th>
<th>Buy Price</th><th>Buy Percent</th><th>Curr. Vol</th><th>Av Vol</th><th>Status</th></tr>
</thead>
<tbody>
</tbody>
</table>
<hr>
$footer
EOF;


<?php
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

if($_POST) {
  $S = new Database($_site);
  vardump($_POST);
  if($_POST['remove']) {
    $remove = strtoupper($_POST['remove']);
    if(!$S->query("delete from stocks.stocks where stock='$remove'")) {
      echo "<h1>Stock Symbol '$remove' Not Found in Database</h1>";
      exit();
    }
    // Now remove all of the items in 'pricedata'
    $S->query("delete from stocks.pricedata where stock='$remove'");
  } else {
    $stock = strtoupper($_POST['stock']);
    $qty = $_POST['qty'];
    $price = $_POST['price'];
    $name = ucwords($_POST['name']);
    $status = $_POST['status'];
    // $bought is a 'date' field with a default of NULL. To not have a date you have to add NULL
    // without the quotes or a real date with quotes.
    $bought = empty($_POST['bought']) ? 'NULL' : "'{$_POST['bought']}'";

    try {
      $S->query("insert into stocks.stocks (stock, qty, price, name, bought, status) ".
                "value ('$stock', '$qty', '$price', '$name', $bought, '$status')");

      // If this is an insert we should update the pricedata table with 100 items.

      $alphakey = "FLT73FUPI9QZ512V";
      $str = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$stock&apikey=$alphakey";

      $h = curl_init();
      curl_setopt($h, CURLOPT_URL, $str);
      curl_setopt($h, CURLOPT_HEADER, 0);
      curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

      $alpha = curl_exec($h);
      $alpha = json_decode($alpha, true); // decode as an array

      $ar = $alpha["Time Series (Daily)"];

      foreach($ar as $k=>$v) {
        $date = $k;
        $price = $v["4. close"];
        //echo "$stock: $date: $price<br>";
        $sql = "insert into stocks.pricedata (stock, date, price) values('$stock', '$date', '$price') ".
               "on duplicate key update date='$date', price='$price'";
        $S->query($sql);
      }
    } catch(Exception $e) {
      error_log("ERRORCODE: " . $e->getCode());
      error_log($e->getMessage());
      
      if($e->getCode() == 1062) { // duplicate key
        // This is an edit so we don't add to the pricedata table.
        $S->query("update stocks.stocks set qty='$qty', ".
                  "price='$price', name='$name', bought=$bought, status='$status' ".
                  "where stock=$stock");  
      } else {
        throw($e);
      }
    }
  }
  
  header("location: stockaddedit.php");
  exit();
}

$S = new $_site->className($_site);
$T = new dbTables($S);

if($_GET['stock']) {
  $stock = $_GET['stock'];
  $S->query("select * from stocks.stocks where stock='$stock'");
  $editrow = $S->fetchrow('assoc');
  $readonly = "style='background-color: gray; color: white' readonly";
}

$h->title = "Stock Register";
$h->banner = "<h1>Stock Register</h1>";

$h->css =<<<EOF
  <style>
input[name='stock'] {
  text-transform: uppercase;
}
input[name='name'] {
  text-transform: capitalize;
}
input[name='remove'] {
  text-transform: uppercase;
}
#stocks td {
  padding: .2rem;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

date_default_timezone_set("America/New_York");

function callback(&$row, &$desc) {
  $row['stock'] = "<a href='stockaddedit.php?stock={$row['stock']}'>{$row['stock']}</a>";

  // The $desc value has the row html with the row keys. For example:
  // "<tr><td>stock</td><td>price</td>...</tr>"
  // The tags can be modified as below. The keys should (must) not be changed because the keys are
  // latter replaced with the $row values for that key.
  
  switch($row['status']) {
    case 'active':
      break;
    case 'watch':
      $desc = preg_replace("/<tr>/", "<tr style='color: red'>", $desc);
      break;
    case 'sold':
      $desc = preg_replace("/<tr>/", "<tr style='background-color: pink'>", $desc);
      break;
  }
}

//$sql = "select * from stocks.stocks";
//list($tbl) = $T->maketable($sql, ['callback'=>'callback', 'attr'=>['border'=>1, 'id'=>'stocks']]);

$sql = "select stock from stocks.pricedata where date='2017-11-21'";
$S->query($sql);
$r = $S->getResult();

while(list($stock) = $S->fetchrow($r, 'num')) {
  echo "$stock<br>";
  $S->query("insert into stocks.stocks (stock) value('$stock')");
}
exit();
echo <<<EOF
$top
$tbl
<h3>Enter New Stock or Edit Stock</h3>
<form method='post'>
<table id='newedit' border='1'>
<tbody>
<tr><th>Stock Symbol</th><td>
  <input type='text' name='stock' value='{$editrow['stock']}' autofocus required $readonly></td></tr>
<tr><th>Buy Price</th><td>
  <input type='number' step='.01' name='price' value='{$editrow['price']}' required></td></tr>
<tr><th>Quantity</th><td>
  <input type='number' step='.01' name='qty' value='{$editrow['qty']}' required></td></tr>
<tr><th>Stock Name</th><td>
  <input type='text' name='name' value='{$editrow['name']}' required></td></tr>
<tr><th>Bought</th><td>
  <input type='date' name='bought' value='{$editrow['bought']}'</td></tr>
<tr><th>Status</th><td>
  <select name='status' value='{$editrow['status']}' required>
  <option>active</option>
  <option>watch</option>
  <option>sold</option>
  </select>
  </td></tr>
</tbody>
</table>
<input type='submit'>
</form>

<h3>Delete Symbol</h3>
<form method='post'>
Enter symbol to delete: <input type='text' name='remove'><br>
<input type='submit'>
</form>

$footer
EOF;

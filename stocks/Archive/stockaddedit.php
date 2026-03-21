<?php
// stockaddedit.php
// This adds and edits stocks in the `stocks` table.
// BLP 2020-10-21 -- removed pricedata. Added back try for duplicate key.

$_site = require_once(getenv("SITELOADNAME"));

function checkUser($S) {
  // I could also look at the fingerprint for my know devices.
  // There are two file at /var/www/bartonphillipsnet/
  // 1) a json file
  // 2) a php file.
  // Each has the same information: a fingerprint and a label for the device.
  // Right now the following is a simpler way to do this.
  
  if($userEmail = explode(":", $_COOKIE['SiteId'])[2]) {
    $sql = "select email from members where email='$userEmail'";

    if(!$S->sql($sql)) {
      echo "<h1>Go Away</h1>";
      exit();
    } else {
      if($S->fetchrow('num')[0] != 'bartonphillips@gmail.com') {
        echo "<h1>Go Away</h1>";
        exit();
      }
    }
  } else {
    echo "<h1>Go Away</h1>";
    exit();
  }
};

if($stock = $_GET['page']) {
  // edit the stock;

  $S = new SiteClass($_site);
  checkUser($S);

  extract(json_decode($_GET['row'], true));
  $price = $price ?? 0;
  $qty = $qty ?? 0;
  
  [$top, $bottom] = $S->getPageTopBottom();

  echo <<<EOF
$top
<form action='./stockaddedit.php' method="post">
<table>
<tr><td>Stock</td><td>$stock</td></tr>
<tr><td>Price</td><td><input name='price' value='$price'></td></tr>
<tr><td>Qty</td><td><input name='qty' value='$qty'></td></tr>
<tr><td>Stock Name</td><td><input name='name' value='$name'></td></tr>
<tr><td>Status</td><td>
  <select name='status' value='$status' required>
  <option>active</option>
  <option>watch</option>
  <option>mutual</option>
  <option>sold</option>
  </select>
<tr>
</table>
<input type='hidden' name='stock' value='$stock'>
<button type='submit' name='submit' value='submit'>Submit</button>
</form>
$bottom
EOF;

  exit();
}

// Form POST from $_GET['page] above.

if($_POST['submit']) {
  $S = new Database($_site);
  checkUser($S);
  extract($_POST);
  
  $S->sql("update stocks set price='$price', qty='$qty', name='$name', status='$status', lasttime=now()
where stock='$stock'");
  // Drop back into GET.
}

// Form POST 'remove' or 'add' below.

if($_POST) {
  $S = new Database($_site);
  checkUser($S); // If not me display 'Go Away' and exit

  // Remove a stock from 'stocks'

  if($_POST['remove']) {
    $remove = strtoupper($_POST['remove']);

    if(!$S->sql("delete from stocks where stock='$remove'")) {
      echo "<h1>Stock Symbol '$remove' Not Found in Database</h1>";
      exit();
    }
  } elseif($_POST['add']) {
    // Add a stock to 'stocks'
    
    $stock = strtoupper($_POST['stock']);
    $qty = $_POST['qty'];
    $price = $_POST['price'];
    $name = ucwords($_POST['name']);
    $status = $_POST['status'];
    // $bought is a 'date' field with a default of NULL. To not have a date you have to add NULL
    // without the quotes or a real date with quotes.
    $bought = empty($_POST['bought']) ? 'NULL' : "'{$_POST['bought']}'";
    $qty = $qty == "" ? 0 : $qty;
    $price = $price == "" ? 0 : $price;
        
    try {
      $S->sql("insert into stocks (stock, qty, price, name, status) ".
              "value ('$stock', '$qty', '$price', '$name', '$status')");
    } catch(Exception $e) {
      if($e->getCode() == 1062) { // duplicate key
        // This is an edit
        $S->sql("update stocks set qty='$qty', ".
                "price='$price', name='$name', status='$status' ".
                "where stock='$stock'");  
      } else {
        throw($e);
      }
    }
  } 
    
  header("location: stockaddedit.php");
  exit();
}

$S = new $_site->className($_site);

checkUser($S); // If not me display 'Go Away' and exit

$T = new dbTables($S);

if($_GET['stock']) {
  $stock = $_GET['stock'];
  $S->sql("select * from stocks where stock='$stock'");
  $editrow = $S->fetchrow('assoc');
  if($editrow['qty'] == 0) $editrow['qty'] = '';
  if($editrow['price'] == 0) $editrow['price'] = '';
  
  $readonly = "style='background-color: gray; color: white' readonly";
}

$S->title = "Stock Register";
$S->banner = "<h1>Stock Register</h1>";

$S->css =<<<EOF
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
#stocks td:nth-child(5) {
  width: 6rem;
}
EOF;

[$top, $footer] = $S->getPageTopBottom();

date_default_timezone_set("America/New_York");

// New callback logic

function callback(&$cellStr, &$row) {
  $cellStr = preg_replace_callback("~(<td class='stock'>)(.*?</td>)~", function($m) use ($row) {
    $stock = $row['stock'];
    $json = json_encode($row);
    return "$m[1] <a href='stockaddedit.php?page=$stock&row=$json'>$stock</a></td>";
  }, $cellStr);
}

$sql = "select * from stocks";

// the true below uses the new makerow() logic. It adds the class names to the columns.

$tbl = $T->maketable($sql, ['callback'=>'callback', 'attr'=>['border'=>1, 'id'=>'stocks']], true)[0];

echo <<<EOF
$top
$tbl
<h3>Enter New Stock or Edit Stock</h3>
<form method='post'>
<table id='newedit' border='1'>
<tbody>
<tr><th>Stock</th><td>
  <input type='text' name='stock' value='{$editrow['stock']}' autofocus required $readonly></td></tr>
<tr><th>Buy Price</th><td>
  <input type='number' step='.01' name='price' value='{$editrow['price']}'></td></tr>
<tr><th>Quantity</th><td>
  <input type='number' step='.01' name='qty' value='{$editrow['qty']}'></td></tr>
<tr><th>Stock Name</th><td>
  <input type='text' name='name' value='{$editrow['name']}'></td></tr>
<th>Status</th><td>
  <select name='status' value='{$editrow['status']}' required>
  <option>active</option>
  <option>watch</option>
  <option>mutual</option>
  <option>sold</option>
  </select><tr>
  </td></tr>
</tbody>
</table>
<input type='hidden' name='add'>
<input type='submit'>
</form>

<h3>Delete Symbol</h3>
<form method='post'>
Enter symbol to delete: <input type='text' name='remove'><br>
<input type='submit'>
</form>

$footer
EOF;

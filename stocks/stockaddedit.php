<?php
// stockaddedit.php
// This adds and edits stocks in the `stocks` table.
// BLP 2020-10-21 -- removed pricedata. Added back try for duplicate key.

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);

function checkUser($S) {
  //echo "cookie: ". $_COOKIE['SiteId']."<br>";
  
  if($userEmail = explode(":", $_COOKIE['SiteId'])[1]) {
    $sql = "select name from members where email='$userEmail'";

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

// Form POST

if($_POST) {
  $S = new Database($_site);
  checkUser($S); // If not me display 'Go Away' and exit

  // Remove a stock from 'stocks'

  if($_POST['remove']) {
    $remove = strtoupper($_POST['remove']);

    if(!$S->query("delete from stocks where stock='$remove'")) {
      echo "<h1>Stock Symbol '$remove' Not Found in Database</h1>";
      exit();
    }
  } else {
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
      @$S->query("insert into stocks (stock, qty, price, name, status) ".
                 "value ('$stock', '$qty', '$price', '$name', '$status')");
    } catch(Exception $e) {
      if($e->getCode() == 1062) { // duplicate key
        // This is an edit
        $S->query("update stocks set qty='$qty', ".
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
  $S->query("select * from stocks where stock='$stock'");
  $editrow = $S->fetchrow('assoc');
  if($editrow['qty'] == 0) $editrow['qty'] = '';
  if($editrow['price'] == 0) $editrow['price'] = '';
  
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
#stocks td:nth-child(5) {
  width: 6rem;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

date_default_timezone_set("America/New_York");

function callback(&$row, &$desc) {
  $row['stock'] = "<a href='stockaddedit.php?stock={$row['stock']}'>{$row['stock']}</a>";
  if($row['qty'] == 0) $row['qty'] = '';
  if($row['price'] == 0) $row['price'] = '';
  
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

$sql = "select * from stocks";
list($tbl) = $T->maketable($sql, ['callback'=>'callback', 'attr'=>['border'=>1, 'id'=>'stocks']]);

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
  <input type='number' step='.01' name='price' value='{$editrow['price']}'></td></tr>
<tr><th>Quantity</th><td>
  <input type='number' step='.01' name='qty' value='{$editrow['qty']}'></td></tr>
<tr><th>Stock Name</th><td>
  <input type='text' name='name' value='{$editrow['name']}'></td></tr>
<tr><th>Status</th><td>
  <select name='status' value='{$editrow['status']}' required>
  <option>active</option>
  <option>watch</option>
  <option>mutual</option>
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

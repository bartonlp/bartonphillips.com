<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

// AJAX 'page' == 'form'

if($_POST['page'] == 'form') {
  $name = $_POST['username'];
  $value = $_POST['id'];
  $ret = ["AJAX Form: name=$name, value=$value"];
  echo json_encode($ret); //"AJAX Form: name=$name, value=$value");
  exit();
}

$h->script =<<<EOF
  <script>
let ret = fetch("testpost.php", {
  body: "test=MY DATA&something=more data",
  method: "POST",
  headers: {
    'content-type': 'application/x-www-form-urlencoded'
  }
}).then(res => res.text());

let test = { test: "MY DATA2", something: "some more data" };

let ret2 = fetch("testpost.php", {
  body: JSON.stringify(test),
  method: "POST",
  headers: {
    'content-type': 'application/json'
  }
}).then(res => res.text());

ret.then(data => console.log("data", data));
ret2.then(data => console.log("data2", data));

  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>Test</h1>
$footer
EOF;


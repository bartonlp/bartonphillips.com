<?php
// This uses testpost.php which is also used by a curl post. See testpost.php for details.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

// AJAX 'page' == 'form'

if($_POST['page'] == 'form') {
  $name = $_POST['username'];
  $value = $_POST['id'];
  $ret = ['name'=>$name, 'value'=>$value];
  echo json_encode($ret);
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
}).then(res => res.json());

let test = { test: "MY DATA2", something: "some more data" };

let ret2 = fetch("testpost.php", {
  body: JSON.stringify(test),
  method: "POST",
  headers: {
    'content-type': 'application/json'
  }
}).then(res => res.json());

let ret3 = fetch("testfetch.php", {
  body: "page=form&username=Barton&id=1",
  method: "POST",
  headers: { 'content-type': 'application/x-www-form-urlencoded' }
}).then(res => res.json());

// ret only outputs to the console
ret.then(data => {console.log("data", data); $("#test1").html(data['type'] + ", " +data['test'] +
", " + data['something']);} );;
// ret2 and ret3 output to id test and test2.
ret2.then(data => {console.log("data2", data); $("#test2").html(data['type'] + ", " + data['test'] +
", " + data['something']);} );
ret3.then(data => {console.log("data3", data); $("#test3").html("Local: " + data['name'] + ", " +  data['value']);} );
  </script>
EOF;

$options = ['http' => [
  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
  'method'  => 'POST',
  'content' => http_build_query(['page'=>'form', 'username'=>'Phillips', 'id'=>'2'])
  ]
];

$context  = stream_context_create($options);

// Now this is going to do a POST!
// NOTE we must have the full url with https!

$ret4 = file_get_contents("https://www.bartonphillips.com/examples/testfetch.php", false, $context);

$ret4 = json_decode($ret4);

$h->title = "js-fetch";
$h->banner = "<h1>$h->title Test</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>Test</h1>
<div id="test1"></div>
<div id="test2"></div>
<div id="test3"></div>
<div>$ret4->name, $ret4->value</div>
$footer
EOF;


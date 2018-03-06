<?php
// Test various 'fetch' instead of AJAX calls.
// Load info from mysitemap.json for use by my framework SiteClass.
// Check SiteClass out at https://github.com/bartonlp/site-class.
// It has full documentation at that site.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

// AJAX 'page' == 'form'

if($_POST['page'] == 'form') {
  $name = $_POST['username'];
  $value = $_POST['id'];
  //error_log("form: ". print_r($_POST, true));
  
  echo "AJAX Form: name=$name, value=$value";
  exit();
}

// AJAX 'page' == 'ajax'

if($_POST['page'] == 'ajax') {
  $data = $_POST['data'];
  echo "AJAX hello World: $data";
  exit();
}

// Get this file for display below.

$promiseText = escapeltgt(file_get_contents("fetch-promise.php"));
$uptest = escapeltgt(file_get_contents("../examples/uptest.php"));
$query = escapeltgt(file_get_contents("../examples/query.ajax.php"));

// Set up the scripts for my framework

$h->extra = <<<EOF
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

<script>
// We use jQuery to do some of the rest of this.

function getIt(type, url, data=undefined) {
  let obj;

  if(type.toLowerCase() == 'post') {
    obj = {
      body: $.param(data),
      method: 'POST',
      headers: {
        'content-type': 'application/x-www-form-urlencoded'
      }
    };
  }

  return fetch(url, obj)
  .then(data => data.text());
}

$(function() {
  // use the function above to do an ajax call. This uses the code at the start.

  getIt('post', 'fetch-promise.php', {page: 'ajax', data: 'this is a test'})
  .then(data => {
    console.log("Success!", data);
    $("#response").html(data);
  })
  .catch(error => {
    console.log("Failed!", error);
    $("#response").html("ERROR from AJAX");
  });
  
  getIt('get', '/examples/uptest.php?test=yes')
  .then(data => {
    data = JSON.parse(data);
    console.log("DATA", data);
    $("#startup").html("fetch: " + data.TEST);
  })
  .catch(error => {
    console.log("ERROR:", error);
  });

  // This is the form

  $("#myform").on("submit", sendForm);

  // This is the 'send' button

  $("#send").on("click", function() {
    // Setup the select for the curtime().

    getIt('post', '/examples/query.ajax.php', {sql: 'select curtime() as data'})
    .then(data => {
      data = JSON.parse(data);
      console.log("query.ajax:", data);
      $("#response").html(data[0].data);
    })
    .catch(err => {
      console.log("query.ajax:", err);
    });
    return false;
  });

  // This is for the 'showpromise' button

  $("#showpromise").on("click", function() {
    // Show the div 'promise'.
    $("#promise").show();
    // Hide the button
    $(this).hide();
    return false;
  });
});

// Send form data via AJAX

function urlencodeFormData(fd) {
  let s = {};

  function encode(s) {
    return encodeURIComponent(s).replace(/%20/g,'+');
  }

  for(let pair of fd) {
    if(typeof pair[1]=='string'){
      s[encode(pair[0])] = encode(pair[1]);
    }
  }
  return s;
}

function sendForm() {
  let formData = new FormData(this);
  formData.append("page", "form");

  getIt("post", this.action, urlencodeFormData(formData))
  .then(data => {
    console.log("DATA:", data);
    $("#response").html(data);
  })
  .catch(err => {
    console.log("form:", err);
  });

  // Don't forget to return false here and not in the 'then' clauses.

  return false;
}
</script>
EOF;

// Setup the css for my framework

$h->css =<<<EOF
  <style>
#promise {
  display: none;
}
  </style>
EOF;

// Get the $top and $footer using my framework

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form id="myform" name="myform" action="fetch-promise.php">
  <input type="text" name="username" value="johndoe">
  <input type="number" name="id" value="123456">
  <input type="submit");">
</form>
<button id="send">Send</button>
<div id="startup"></div>
<div id="response"></div>
<hr>
<button id="showpromise">View the file
<b>fetch-promise.php</b>,<b>query.ajax.php</b> and <b>uptest.php</b></button>
<div id="promise">
<p>fetch-promise.php</p>
<pre class='brush: php'>
$promiseText
</pre>
<p>query.ajax.php</p>
<pre class='brush: php'>
$query
</pre>
<p>uptest.php</p>
<pre class='brush: php'>
$uptest
</pre>
</div>
$footer
EOF;

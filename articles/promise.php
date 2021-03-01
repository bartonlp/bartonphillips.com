<?php
// Test various AJAX and Promise calls.
// This uses ../examples/uptime.php and
// ..examples/query.ajax.php for Ajax calls.

// Load info from mysitemap.json for use by my framework SiteClass.
// Check SiteClass out at https://github.com/bartonlp/site-class.
// It has full documentation at that site.
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

// AJAX 'page' == 'form'

if($_POST['page'] == 'form') {
  $name = $_POST['username'];
  $value = $_POST['id'];
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

$promiseText = escapeltgt(file_get_contents("promise.php"));
$uptest = escapeltgt(file_get_contents("../examples/uptest.php"));
$query = escapeltgt(file_get_contents("../examples/query.ajax.php"));

// Set up the scripts for my framework

$h->extra = <<<EOF
<script src="https://bartonphillips.net/js/syntaxhighlighter.js"></script>
<link rel='stylesheet' href="https://bartonphillips.net/css/theme.css">

<script>
// RAW javascript to do the promise
var CORSflag = true;

function get(url, type, data) {
  // Return a new promise.
  return new Promise(function(resolve, reject) {
    // Do the usual XHR stuff
    var req = new XMLHttpRequest();
    req.open(type, url, true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    req.onload = function() {
      if(req.status == 200) {
        // Resolve the promise with the response text
        resolve(req.response);
      } else {
        // Otherwise reject with the status text
        // which will hopefully be a meaningful error
        reject(Error(req.statusText));
      }
    };

    // Handle network errors
    req.onerror = function() {
      reject(Error("Network Error"));
    };

    // Make the request
    var d = $.param(data);
    //console.log("data: ", d);
    req.send(d);
  });
}

// We use jQuery to do some of the rest of this.

jQuery(document).ready(function($) {
  // use the function above to do an ajax call. This uses the code at the start.

  get('promise.php', 'post', {page: 'ajax', data: 'this is a test'}).then(function(response) {
    console.log("Success!", response);
    $("#response").html(response);
    return false;
  }, function(error) {
    console.log("Failed!", error);
    $("#response").html("ERROR from AJAX promise.php");
    return false;
  });

  // This is the 'send' button

  $("#send").click(function() {
    // Setup the select for the curtime().

    sendText('select curtime() as data');
    return false;
  });

  // This is for the 'showpromise' button

  $("#showpromise").click(function() {
    // Show the div 'promise'.
    $("#promise").show();
    // Hide the button
    $(this).hide();
    return false;
  });

  // Make an ajax call to another file. Returns 'This is from RPI' which is my RPI server at home.
  if(CORSflag) {
    $.ajax({
      url: '../examples/uptest.php',
      data: { test: 'yes' },
      dataType: 'json'
    }).done(function(d) {
      console.log("DATA", d);
      $("#startup").html("AJAX: " + d.TEST);
      return false;
    }).error(function() {
      console.log("ERROR");
      return false;
    });
  }
});

// Another RAW Javascript AJAX call to another file on the server. When called with the select
// statement above it returns the current time.

function sendText(txt) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '../examples/query.ajax.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(e) {
    if (this.status == 200) {
      var newtxt = JSON.parse(this.responseText);
      console.log("newtxt", newtxt);
      var date = newtxt[0].data;
      $("#response").html(date);
    }
  };

  xhr.send("sql="+txt);
}

// Send form data via AJAX via the input line:
// <input type="submit" onclick="return sendForm(this.form);">

function sendForm(form) {
  var formData = new FormData(form);
  formData.append("page", "form");

  var xhr = new XMLHttpRequest();
  xhr.open('POST', form.action, true);
  xhr.onload = function(e) {
    $("#response").html(e.currentTarget.response);
  };

  xhr.send(formData);
  return false;
}

// Check if CORS is available for this site.
// Another RAW Javascript function.

var req = new XMLHttpRequest();
req.open("GET", "../examples/uptest.php" , true);
req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

req.onload = function() {
  if(req.status == 200) {
    // Resolve the promise with the response text
    console.log("CORS OK");
  } else {
    // Otherwise reject with the status text
    // which will hopefully be a meaningful error
    console.log(req.statusText);
  }
};

// Handle network errors

req.onerror = function() {
  console.log("Network Error");
  CORSflag = false;
};

req.send();

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
<form id="myform" name="myform" action="promise.php">
  <input type="text" name="username" value="johndoe">
  <input type="number" name="id" value="123456">
  <input type="submit" onclick="return sendForm(this.form);">
</form>
<button id="send">Send</button>
<div id="startup"></div>
<div id="response"></div>
<hr>
<button id="showpromise">View the file
<b>promise.php</b>, <b>query.ajax.php</b> and <b>uptest.php</b></button>
<div id="promise">
<p>promise.php</p>
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

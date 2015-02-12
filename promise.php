<?php
// BLP 2014-05-12 -- http://www.html5rocks.com/en/tutorials/file/xhr2/
// header("Access-Control-Allow-Origin: *");

require_once("/var/www/includes/siteautoload.class.php");
$S = new Blp; // takes an array if you want to change defaults

if($_POST['page'] == 'form') {
  $name = $_POST['username'];
  $value = $_POST['id'];
  echo "Form: name=$name, value=$value";
  exit();
}

if($_POST['page'] == 'ajax') {
  $data = $_POST['data'];
  echo "hello World: $data";
  exit();
}

$h->extra = <<<EOF
<script>
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
    req.send(d);
    return false;
  });
}

jQuery(document).ready(function($) {
  get('promise.php', 'post', {page: 'ajax', data: 'this is a test'}).then(function(response) {
    console.log("Success!", response);
    $("#response").html(response);
  }, function(error) {
    console.log("Failed!", error);
  });

  $("button").click(function() {
    sendText('test string');
    return false;
  });

  $.ajax({
    url: 'http://bartonphillips.dyndns.org/uptest.php',
    data: { test: 'yes' },
    dataType: 'json'
  }).done(function(d) {
    console.log("DATA", d);
  });
});

function sendText(txt) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'test.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function(e) {
    if (this.status == 200) {
      console.log(this.responseText);
      $("#response").html(this.responseText);
    }
  };

  xhr.send("input="+txt);
}

function sendForm(form) {
  var formData = new FormData(form);
  formData.append("page", "form");

  var xhr = new XMLHttpRequest();
  xhr.open('POST', form.action, true);
  xhr.onload = function(e) {
    console.log("Response", e);
    $("#response").html(e.currentTarget.response);
  };

  xhr.send(formData);
  return false;
}

function createCORSRequest(method, url) {
  var xhr = new XMLHttpRequest();
  if ("withCredentials" in xhr) {
    // Check if the XMLHttpRequest object has a "withCredentials" property.
    // "withCredentials" only exists on XMLHTTPRequest2 objects.
    xhr.open(method, url, true);
  } else if (typeof XDomainRequest != "undefined") {
    // Otherwise, check if XDomainRequest.
    // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
    xhr = new XDomainRequest();
    xhr.open(method, url);
  } else {
    // Otherwise, CORS is not supported by the browser.
    xhr = null;
  }
  return xhr;
}

var xhr = createCORSRequest('GET', 'http://www.bartonphillips.dyndns.org/uptest.php');
if(!xhr) {
  throw new Error('CORS not supported');
} else {
  console.log("CORS OK");
}
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form id="myform" name="myform" action="promise.php">
  <input type="text" name="username" value="johndoe">
  <input type="number" name="id" value="123456">
  <input type="submit" onclick="return sendForm(this.form);">
</form>
<button>Send</button>

<div id="response"></div>
<hr>
$footer
EOF;

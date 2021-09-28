<?php
// This is an example of sending a notification. It uses the Notification object.
// When  the notification is clicked on we got to my home page.
// This is attempt to do a git st and if it has any file send a notify.

if($_GET['page']) {
  $ret = '';

  $any = false;
  
  foreach(['/vendor/bartonlp/site-class', '/applitec', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/bartonphillips.org', '/granbyrotary.org', '/messiah'] as $site) {
    chdir("/var/www/$site");
    exec("git status", $out);
    $out = implode("\n", $out);
    if(!preg_match('/working directory clean/s', $out)) {
      $any = true;
    }
  }

  echo $any === true ? "true" : "false";
  exit();
}

$_site = require_once(getenv('SITELOADNAME'));
ErrorClass::setDevelopment(true);
//$S = $_site->className($_site);

echo <<<EOF
<!DOCTYPE html>
<head>
<script>
function notifyMe(msg) {
  // Let's check if the browser supports notifications
  if (!("Notification" in window)) {
    alert("This browser does not support desktop notification");
  } else if(Notification.permission === "granted") {
    //console.log("hi 0");

    var notification = new Notification("Hi there!", {
          body: msg,
          icon: "https://bartonphillips.net/images/favicon.ico"
    });
  } else if(Notification.permission !== "denied") {
    //console.log("hi ask permision 2");

    Notification.requestPermission(function (permission) {
      // If the user accepts, let's create a notification
      //console.log("hi 3");
      if(permission === "granted") {
        var notification = new Notification("First Time!", {
          body: msg,
          icon: "https://bartonphillips.net/images/favicon.ico"
        });
      }
    });
  }

  notification.onclick = function(event) {
    event.preventDefault(); // prevent the browser from focusing the Notification's tab
    window.open('https://www.bartonphillips.com/gitstatus.php', '_blank');
    notification.close();
  }
}

function sendText() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'notify-git.php?page=true', true);
  //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  // Send the text to the worker.ajax.php

  xhr.send();

  // Get the information from our xhr.send().

  xhr.onload = function(e) {
    var status = document.querySelector("#status");

    if(this.status == 200) {
      console.log("responseText", this.responseText);
      if(this.responseText == 'true') {
        notifyMe("Your file are not up to date");
        //let text = document.createTextNode('You have been Notified');
        status.innerHTML = 'You have been Notified';
      } else {
        status.innerHTML = 'Everything is up to date';
      }
    }
  }
}
</script>
</head>
<body>
<h1>Demo of Notify</h1>
<p>If this is the first time you will be asked it you <b>Allow or Disallow</b> notifications
on this computer</p>
<button>Click Me to Send a Notification</button>
<div id='status'></div>
<script>
var but = document.querySelector('button');
but.addEventListener("click", function(e) {
  sendText();
});
</script>
</body>
</html>
EOF;

<?php
// This is an example of sending a notification. It uses the Notification object.
// When  the notification is clicked on we got to my home page.

echo <<<EOF
<!DOCTYPE html>
<head>
<script>
function notifyMe() {
  // Let's check if the browser supports notifications
  if (!("Notification" in window)) {
    console.log("This browser does not support desktop notification");
  }

  // Let's check whether notification permissions have already been granted
  else if (Notification.permission === "granted") {
    // If it's okay let's create a notification
    console.log("hi 0");

    var notification = new Notification("Hi there!", {
          body: "This is the body",
          icon: "https://bartonphillips.net/images/favicon.ico",
          image: "https://bartonphillips.net/images/Octocat.png"});

    console.log("hi 1:", notification);

    notification.onclick = function(event) {
      event.preventDefault(); // prevent the browser from focusing the Notification's tab
      window.open('https://www.bartonphillips.com', '_blank');
      notification.close();
    }
  }

  // Otherwise, we need to ask the user for permission
  else if (Notification.permission !== "denied") {
    console.log("hi ask permision 2");
    Notification.requestPermission(function (permission) {
      // If the user accepts, let's create a notification
      console.log("hi 3");
      if (permission === "granted") {
        var notification = new Notification("First Time!", {
          body: "Your fist time here, thanks",
          icon: "https://bartonphillips.net/images/favicon.ico",
          image: "https://bartonphillips.net/images/Octocat.png"});

        console.log("hi first time 4:", notification);

        notification.onclick = function(event) {
          event.preventDefault(); // prevent the browser from focusing the Notification's tab
          window.open('https://www.bartonphillips.com', '_blank');
          notification.close();
        }
      }
    });
  }
  console.log("hi done 5");

  // At last, if the user has denied notifications, and you 
  // want to be respectful there is no need to bother them any more.
}
</script>
</head>
<body>
<h1>Demo of Notify</h1>
<p>If this is the first time you will be asked it you <b>Allow or Disallow</b> notifications
on this computer</p>
<button>Click Me to Send a Notification</button>
<script>
var but = document.querySelector('button');
but.addEventListener("click", function(e) {
  notifyMe();
});
</script>
</body>
</html>
EOF;

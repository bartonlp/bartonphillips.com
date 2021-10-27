// index.js
// This is the JavaScript for index.php
// BLP 2021-03-26 --
// This is installed after the footer because we set $b->script not $h.
// As a result we do not need to do jQuery(document).ready(function($) {

'use strict';

// If we have adminstuff we need another column.

if(window.CSS) {
  if(CSS.supports('display', 'grid') && $("#adminstuff").length != 0) {
    $("#grid-section").css("grid-template-columns", "repeat(4, 1fr)");
  }
}

// Local date/time for 'Today is' & 'Your Time is'. Uses phpdate.js
// loaded in index.php

setInterval(function() {
  var d = date("l F j, Y");
  var t = date("H:i:s T"); // from phpdate.js
  $("#datetoday").html("<span class='green'>"+
                       d+"</span><br>Your Time is: <span class='green'>"+
                       t+"</span>");
}, 1000);

// We set this in index.php, if it is true we will notify me.
// BLP 2021-03-26 -- we have set doGit to "" in the bottom script in
// index.php so we will NOT do this.

if(doGit !== "") {
  function notifyMe(msg) {
    // Let's check if the browser supports notifications
    if (!("Notification" in window)) {
      alert("This browser does not support desktop notification");
    } else if(Notification.permission === "granted") {
      var notification = new Notification("Hi there!", {
        body: msg,
        icon: "https://bartonphillips.net/images/favicon.ico"
      });
    } else if(Notification.permission !== "denied") {
      Notification.requestPermission(function (permission) {
        // If the user accepts, let's create a notification
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
      window.open('https://www.bartonphillips.com/bartonlp/gitstatus.php', '_blank');
      notification.close();
    }
  }

  notifyMe("Your files are not up to date");
}

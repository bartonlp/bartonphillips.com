// index.js
// This is the JavaScript for index.php

jQuery(document).ready(function($) {
  var weewx = '';

  if($("#adminstuff").length != 0) {
    $("#grid-section").css("grid-template-columns", "repeat(4, 1fr)");
  }
  
  // Local date/time for 'Today is' & 'Your Time is'
  
  setInterval(function() {
    var d = date("l F j, Y");
    var t = date("H:i:s T"); // from phpdate.js
    $("#datetoday").html("<span class='green'>"+
                         d+"</span><br>Your Time is: <span class='green'>"+
                         t+"</span>");
  }, 1000);

  // We set this in PHP above and now if it is true we will notify me.

  if(doGit == true) {
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

    notifyMe("Your files are not up to date");
  }
});

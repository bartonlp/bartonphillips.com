// JavaScript for showErrorLog2.php

'use strict';

let win = null;
const showErrorLogUrl = window.location.pathname;
console.log(showErrorLogUrl);
console.log("page=" + file);
console.log("Last Delete time: ", del);
let tbl;
let hdr;
let nodata;

$("#del-time").html("<p>Last Delete Time: " + del + "</p>");

// I am clicking on id or ip. I need the myOtherTab to be set via localStoarage.

let myOtherTab = null;

$("body").on("click",".ip,.id", function(e) {
  const idOrIp = $(this).text(); //.split("=")[1];
  const cl = e.currentTarget.className;

  const where = "where " +cl+"='" +idOrIp+ "'";
  const and = "and lasttime>current_date() -interval 5 day";
  const by = "order by lasttime desc";

  const data = [where, and, by];

  let recievedResponse = false;

  const channel = new BroadcastChannel('myOtherTab');

  function openOrReuseMyOtherTab(data) {
    recievedResponse = false;

    channel.postMessage({ type: 'is_open?' });

    // Wait 500ms to see if myprogram3.php responds
    setTimeout(() => {
      if(!recievedResponse) {
        data = JSON.stringify(data);
        myOtherTab = window.open('findip2.php?data=' + data, 'myOtherTab');
        myOtherTab.focus();
      } else {
        // Send data once the tab is confirmed
        sendDataToMyOtherTab(data);
      }
    }, 500);
  }

  // Listen for responses from findip2.php

  channel.onmessage = (event) => {
    if(event.data.type === 'is_open') {
      recievedResponse = true; // findip2.php is open!
    }
  };

  function sendDataToMyOtherTab(data) {
    data = JSON.stringify(data);
    channel.postMessage({ type: 'update', payload: data });
    setTimeout(() => {
      channel.postMessage({ type: "focus_request" });
    }, 50); // Delay of 50ms
  }

  openOrReuseMyOtherTab({ message: data });

  $(this).css({ background: "green", color: "white"});
});

// This is the AJAX function that gets the data from 'newdata'

function doAjax(dly) {
  // The CRON job runs every 1/4 of an hour. If we don't wait a little then the CRON job and this
  // will run at the same time and we will have to wait till the next 1/4 hour before the refresh
  // happens.

  let delay;

  if(dly == 15) {
    delay = 30000; // Wait for 30 second and then do the ajax
  } else {
    delay = 0; // Do the ajax imediatly.
  }

  setTimeout(function() {
    $.ajax({
      url: showErrorLogUrl,
      data: { page: "newdata", file: file },
      type: "post",
      success: function(data) {
        // Post data to #output

        [data, nodata] = JSON.parse(data);

        if(nodata == "true") {
          tbl = data;
        } else {
          tbl = `
                <table id="table" border="1">
                                         <thead>
                                         ${hdr}
          </thead>
              <tbody>
              ${data}
          </tbody>
              </table>
              `;
        }

        $("#output").html(tbl);
        if(nodata == 'true') {
          $("#table thead tr").remove();
        }
      },
      error: function(err) {
        console.log("ERROR:", err);
      }
    });
  }, delay);
}

// Set up a timer
// Check which log we are using and set time accordingly.

function waitForPeriod(dly) {
  const now = new Date();
  const minutes = now.getMinutes();
  const seconds = now.getSeconds();

  // Calculate the delay until the next quarter-hour
  const delay = (dly - (minutes % dly)) * 60 * 1000 - seconds * 1000;

  console.log("Waiting for", delay / 1000, "seconds until the next period.");

  setTimeout(function() {
    doAjax(dly);
    waitForPeriod(dly);
  }, delay);
}

if(file == "/var/www/PHP_ERRORS_CLI.log") {
  waitForPeriod(15); // CRON updates this every 15 minutes. We wait a minute more.
} else {
  waitForPeriod(1);
}

hdr = "<th>Time</th><th>Item</th><th colspan='5'>Information</th></tr>";

console.log("nodata=$nodata");

nodata = "$nodata";

if(nodata == 'true') {
  tbl = dataStr;
} else {
  tbl = `
        <table id="table" border="1">
                                 <thead>
                                 ${hdr}
  </thead>
      <tbody>
      ${dataStr}
  </tbody>
      </table>
      `;
}

$("#output").html(tbl);
if(nodata == "true") {
  $("#table thead tr").remove();
}

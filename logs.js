// This is used by logs.php. It also loads findip.php via
// window.open().

'use strict';

// There are global to this file.

const startLogName = "/var/www/PHP_ERRORS.log"; // Default log file
const logsUrl = 'logs.php'; // Name of the url used in Ajax calls.
let logFile = startLogName
let idOrIp; // The ip or id value.

function switchLog() {
  logFile = document.getElementById("log-selector").value;
  localStorage.removeItem('logFilter');

  let date = null;

  if(logFile == startLogName) {
    date = localStorage.getItem("err-del");
  } else {
    date = localStorage.getItem("err-cli-del");
  }
  if(date) $("#del-time").html(`Last time deleted: ${date}`);

  loadLogs();
}

function deleteLog() {
  $.ajax({
    url: logsUrl,
    data: { delete: 'delete', delname: logFile },
    type: "post",
    success: function(data) {
      console.log("date: ", data);
      if(logFile == startLogName) {
        localStorage.setItem("err-del", data);
      } else {
        localStorage.setItem("err-cli-del", data);
      }
      $("#del-time").html(`Last time deleted: ${data}`);
      logFile = $("#log_name").text();
      loadLogs();
    },
    error: function(err) {
      console.log("ERROR: ", err);
    }
  });
}
         
function loadLogs(callback) {
  $("#log_name").html(logFile);

  fetch(`logs.php?action=show_logs&logFile=${logFile}`)
  .then(response => response.json())
  .then(data => {
    //console.log("data:", data);
    document.getElementById("log-content").innerHTML = `<table id='table' border='1'>${data[0]}</table>`;
    if(callback) callback();
    waitForPeriod(1);
  })
  .catch(error => console.error("Error loading logs:", error));
}

function refresh() {
  loadLogs(() => {
    const saved = JSON.parse(localStorage.getItem('logFilter') || '{}');
    const { item, idOrIp, toggled } = saved;

    if (toggled === false && item && idOrIp) {  
      $("tr").hide(); // Hide all.

      $("tr").each(function() {
        if($(this).find("td:nth-of-type("+item+") span").text().trim() === idOrIp) {
          $(this).show();
        }
      });
    }
  });
}

function waitForPeriod(dly) {
  const now = new Date();
  const minutes = now.getMinutes();
  const seconds = now.getSeconds();

  // Calculate the delay until the next quarter-hour
  const delay = (dly - (minutes % dly)) * 60 * 1000 - seconds * 1000;

  console.log("Waiting for", delay / 1000, "seconds until the next period.");

  setTimeout(function() {
    refresh();
  }, delay);
}

// Use jQuery for rest

$("body").on("click",".ip,.id", function(e) {
  idOrIp = $(this).text(); // Id or Ip values from td 3 or 4.
  const cl = e.currentTarget.className; // This is the class 'id' or 'ip'

  if(e.ctrlKey) {
    // I want to toggle between showing only rows with this value and
    // showing all rows.

    let flag;
    const saved = JSON.parse(localStorage.getItem('logFilter'));
    
    if(saved === null) {
      flag = true;
    } else {
      flag = saved.toggled;
    }    
    let item;
    
    if(flag) {
      // true

      $("tr").hide();

      const td = $(this).closest('td');
      item = $(td)[0].cellIndex + 1;

      // Look at each tr.
      
      $("tr").each(function() {
        // If the tr has a td with a span whos class is 'cl' (ie
        // either 'id' or 'ip' then get the text and compare it to
        // idOrIp.
        
        if($(this).find(`td span[class='${cl}']`).text().trim() === idOrIp) {
          // Show only the hidden items that match idOrIp.
          
          $(this).show();
        }
      });
    } else {
      // false. Show everything once more.
      
      $('tr').show();
    }
    flag = !flag; // Flip flag.
    $(this).data('toggled', flag); // Save it

    // Then really save all the important items in localStorage.
    
    localStorage.setItem('logFilter', JSON.stringify({
      item: item,
      idOrIp: idOrIp,
      toggled: flag // After we have removed and updated
    }));

    e.stopPropagation();
  } else {
    const where = "where " +cl+"='" +idOrIp+ "'";
    const and = "and lasttime>current_date() -interval 5 day";
    const by = "order by lasttime desc";
    const data = JSON.stringify([where, and, by]);
    $.ajax({
      url: logsUrl,
      data: { action: "find_ip", data: data },
      type: "get",
      success: function(ok) {
        console.log("data: ", ok);
        window.open(`/findip.php?data=${data}`, "mytab");
      },
      error: function(err) {
        console.log("ERROR: ", err);
      }
    });
    e.stopPropagation();
  }
});

// Start up now that the DOM is loaded.
// Start: Set del-time

switchLog();

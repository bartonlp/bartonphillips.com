// logs.php is a link in adminsites.php that says "Show Info Logs"
// This is used by logs.php. It also loads findip.php via
// window.open().

'use strict';

// There are global to this file.

// The names of the files.

const php_errors_log = "/var/www/PHP_ERRORS.log"; 
const php_errors_cli_log = "/var/www/PHP_ERRORS_CLI.log";
const loginfo = "/var/www/data/info.log";
const interaction_table = "TABLE"; // Special case, this is for the MySql interaction table.

const logsUrl = 'logs.php'; // Name of the url used in Ajax calls.
let logFile = php_errors_log;
let idOrIp; // The ip or id value.

// Switch log files. There are three files and one table.

function switchLog() {
  logFile = document.getElementById("log-selector").value; // Get the logFile name.
  localStorage.removeItem('logFilter');

  let date = null;

  switch(logFile) {
    case php_errors_log:
      date = localStorage.getItem("err-del");
      break;
    case interaction_table:
      date = localStorage.getItem("err-table-del");
      break;
    case php_errors_cli_log:
      date = localStorage.getItem("err-cli-del");
      break;
    case loginfo:
      date = localStorage.getItem("loginfo");
      break;
  }
  
  if(date) $("#del-time").html(`Last time deleted: ${date}`);

  loadLogs();
}

function deleteLog() {
  $.ajax({
    url: logsUrl,
    data: { delete: 'delete', delname: logFile },
    type: "post",
    success: function(date) {
      console.log("date: ", date);
      
      switch(logFile) {
        case php_errors_log:
          localStorage.setItem("err-del", date);
          break;
        case interaction_table:
          localStorage.setItem("err-table-del", date);
          break;
        case php_errors_cli_log:
          localStorage.setItem("err-cli-del", date);
          break;
        case loginfo:
          localStorage.setItem("loginfo", date);
          break;
      }

      $("#del-time").html(`Last time deleted: ${date}`);
      
      refresh();
    },
    error: function(err) {
      console.log("ERROR: ", err);
    }
  });
}
         
function loadLogs(callback) {
  $("#log_name").html(logFile);

  fetch(`${logsUrl}?action=show_logs&logFile=${logFile}`)
  .then(response => response.json())
  .then(data => {
    //console.log("data:", data[0]);
    document.getElementById("scroll-wrapper").innerHTML = data[0];
    if(callback) callback();
    waitForPeriod(1);
  })
  .catch(error => console.log('Error loading logs:', error));
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
  const cl = $(this).hasClass('ip') ? 'ip' : 'id';

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
        //console.log("data: ", data);
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

// table pop up
/*
function showViewportSize() {
  const vp = document.createElement('div');
  vp.style.position = 'fixed';
  vp.style.top = '0';
  vp.style.left = '0';
  vp.style.background = 'rgba(255,255,255,0.8)';
  vp.style.padding = '2px 5px';
  vp.style.zIndex = '9999';
  document.body.appendChild(vp);

  function updateSize() {
    vp.textContent = `(${window.innerWidth}px)x(${window.innerHeight}px)`;
  }

  window.addEventListener('resize', updateSize);
  updateSize();
}
showViewportSize();
*/

(function() {
  const wrapper = document.getElementById("scroll-wrapper");

  function centerScrollWrapper() {
    const rect = wrapper.getBoundingClientRect();
    const scrollY = window.scrollY + rect.top - (window.innerHeight - rect.height) / 2;
    window.scrollTo({ top: scrollY, behavior: 'smooth' });
  }

  function lockScroll() {
    // Only for desktop
    if (!('ontouchstart' in window)) {
      document.body.style.overflow = 'hidden';
    }
  }

  function unlockScroll() {
    document.body.style.overflow = '';
  }

  function setupScrollControl() {
    if (window.innerWidth < 1600) {
      // Lock/unlock scroll on hover (desktop)
      wrapper.addEventListener('mouseenter', lockScroll);
      wrapper.addEventListener('mouseleave', unlockScroll);

      // Center on first interaction
      wrapper.addEventListener('mouseenter', centerScrollWrapper);

      // For mobile: center on first tap only
      wrapper.addEventListener('touchstart', centerScrollWrapper);
    } else {
      wrapper.removeEventListener('mouseenter', lockScroll);
      wrapper.removeEventListener('mouseleave', unlockScroll);
      wrapper.removeEventListener('mouseenter', centerScrollWrapper);
      wrapper.removeEventListener('tourchstart', centerScrollWrapper);
    }
  }

  window.addEventListener('DOMContentLoaded', setupScrollControl);
  window.addEventListener('resize', setupScrollControl);
})();
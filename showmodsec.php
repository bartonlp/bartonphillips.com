<?php
// Show the modsecurity log file.

$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$logFile = '/var/log/apache2/modsec_audit.log';

(($fh = fopen($logFile, 'r')) !== false) || exit(vardump("Error", error_get_last()));

// AJAX POST

if($_POST['page'] == "getdata") {
  $lines = null;
  if(preg_match("~--[a-z0-9]+-~", $_POST['trans'], $m) !== 1) {
    error_log("showmodsec: line=" . __LINE__ . ", BAD or NO MATCH");
    echo "BAD or NO Match<br>";
  } else {
    $trans = $m[0];
  }
  
  while(($line = fgets($fh)) !== false) {
    if(preg_match("~{$trans}A--~", $line) === 1) {
      $capture = true;
      $lines .= "{$trans}A--";
      continue;
    }
    if($capture) {
      $lines .= "$line";
      if(preg_match("~{$trans}Z--~", $line) === 1) {
        $lines .= "*****************************************\n";
        $capture = false;
        continue;
      }
    }
  }

  echo $lines;
  exit();
}

// Normal GET
// Gather the data from the log file.

$ar1 = ["(GET) (.*) HTTP/(\d\.\d)", "(POST) (.*) HTTP/(\d\.\d)", "(HEAD) (.*) HTTP/(\d\.\d)"];
$ar2 = ["HTTP/1.1 500", "HTTP/1.1 404", "HTTP/1.1 200", "HTTP/1.1 (40. .*)"];
$ar3 = ["Host: .*", "User-Agent: .*"];

$flag = false;
$ret = null;

while(($line = fgets($fh)) !== false) {
  if(preg_match("~^(--[a-f0-9]+-)A--~", $line, $m) === 1) {
    $trans = $m[1];

    $flag = false; // Set flag so we get the first line.

    $line = fgets($fh); // The second line has the ip address
    
    if(preg_match("~^\[(\d\d)/(\w{3})/(\d{4}):(.*?) -.*?\].*?(\d+\.\d+\.\d+\.\d+) ~", $line, $m) !== 0) {
      $date = "{$m[2]} {$m[1]}, {$m[3]} {$m[4]}";
      $ip = $m[5];
    }

    continue;
  }

  // If I find the Z group then restart at the next A group.
  
  if(preg_match("~{$trans}Z~", $line) === 1) {
    // If we have $transaction then we had a 40? in the F group.
    // Otherwise we got a 500, 404 or a 200 and we don't process those.
    
    if($transaction) {
      // Make the output line
      
      $ret .= "<span class='transno'>$transaction</span><br>$date<br>IP: <span class='ip'>$ip</span><br>$matchb<br>$matchf<br>";
      if($matchb1) {
        if(str_contains($matchb1, "Host: 192.241.132.229:443")) {
          $ret .= "<span class='me'>$matchb1</span><br>"; // Host if me
        } else {
          $ret .= "$matchb1<br>"; // Host
        }
      }
      if($matchb2) {
        $ret .= "$matchb2<br>"; // User-Agent
      }
      if($anomaly) {
        $ret .= "Score: $anomaly<br>"; // Score, the overall number and the rest of the line till the rt praren.
      }
      $ret .= "=======================================<br><br>";
    }
    continue;
  }

  // Look for the first occurance of 'Total Inbound Score' in the H group.
  // I don't sepcificall need to find the H group here.
  
  if($flag === false && (preg_match("~Total Inbound Score: (\d+).*(SQLI.*?)\)~", $line, $m) === 1)) {
    $anomaly = "{$m[1]} {$m[2]}";
    $flag = true; // Don't trigger again until Z.
    continue;
  }

  // Look for the B group and process it.
  
  if(preg_match("~{$trans}B~", $line, $m) === 1) {
    $line = fgets($fh); // GET or POST
    $matchb = $matchb1 = $matchb2 = null;
    
    foreach($ar1 as $needle) {
      if(preg_match("~$needle~", $line, $m) === 1) {
        $matchb = "{$m[1]}: {$m[2]}"; // this is the GET/POST and the url

        if($m[3] == "1.0") { // If it is 1.0 then disregard the next line.
          break;
        }
      }
    }
    
    $tmp = [];
    $line = fgets($fh);

    // Look for Host or User-Agent.
    
    foreach($ar3 as $val) {
      if(preg_match("~$val~", $line) === 1) {
        $tmp[] = $line; // could be Host or User-Agent
        break;
      }
    }

    $line = fgets($fh);

    // Look for Host or User-Agent in next line.
    
    foreach($ar3 as $val) {
      if(preg_match("~$val~", $line) ===1) {
        $tmp[] = $line;
        break;
      }
    }

    // Depending or the B group we may have one or both Host and User-Agent.
    
    $matchb1 = $tmp[0];
    $matchb2 = $tmp[1];
    continue;
  }

  // Look for the F group and process it.
  
  if(preg_match("~{$trans}F~", $line) === 1) {
    $line = fgets($fh);
    $matchf = null;

    // Looking for 500, 404, 200 or 40? (usually 403)
    
    foreach($ar2 as $key=>$needle) {
      $transaction = null;
      if(preg_match("~$needle~", $line, $m) === 1) {
        if($key == 3) { // The first three are 500, 404 and 200
          // This is 40? (usually 403)
          
          $transaction =  "{$trans}A--"; // Set the transaction so it prints at Z
          $matchf = "{$m[1]}";
        }
        break; // break out of foreach and continue to top.
      }
    }
  }
  // This is an implied continue.
}

$S->title = "Show ModSec Log";
$S->banner = "<h1>$S->title</h1>";

$S->b_inlineScript = <<<'EOF'
function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

let myOtherTab = null;

$(".ip").on("click", function(e) {
  const ip =$(this).text();
  console.log("ip: " + ip);

  const where = "where ip='" +ip+ "'";
  const and = "and lasttime>current_date() -interval 5 day";
  const by = "order by lasttime desc";

  //const data = JSON.stringify({ message: [where, and, by] }); 
  const data = [where, and, by];
  //window.open("findip.php?data=" + data, "_blank");

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
    myOtherTab.focus();
  }

  openOrReuseMyOtherTab({ message: data });

  $(this).css({ background: "green", color: "white"});
});

$(".transno").on("click", function(e) {
  const transno = $(this).text(); // This is the full trans number --nnnnnnn-a--. 'getdata' removes the a--

  $.ajax({
    url: "/showmodsec.php",
    data: {page: "getdata", trans: transno},
    type: "post",
    success: function(data) {
      console.log("transno:", transno);
      const newpage = `
<!DOCTYPE html>
<html>
<title>Full Modsec for ${transno}</title>
<style>
body {
  background: hsla(144, 100%, 95%, 0.6);
  font-size: 20px;
}
pre {
  padding-left: 5px; white-space: break-spaces;
}
</style>
</head>
<body>
<center><h1>Full Listing for ${transno}</h1></center><hr>
<pre>${data}</pre>
</body>
</html>
`;
      const win = window.open("", "_blank");
      win.document.write(newpage);
      win.document.close();
    },
    error: function(err) {
      console.log("Error: ", err);
    }
  });
});
EOF;

$S->css = <<<EOF
.ip, .transno { cursor: pointer; }
.me { color: red; }
EOF;

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
$ret
<hr>
$bottom
EOF;

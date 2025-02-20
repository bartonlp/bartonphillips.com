<?php
// Show the modsecurity log file.

$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$ar1 = ["(GET) (.*) HTTP/(\d\.\d)", "(POST) (.*) HTTP/(\d\.\d)", "(HEAD) (.*) HTTP/(\d\.\d)"];
$ar2 = ["HTTP/1.1 500", "HTTP/1.1 404", "HTTP/1.1 200", "HTTP/1.1 (40. .*)"];
$ar3 = ["Host: .*", "User-Agent: .*"];

$flag = false;
$ret = null;

$logFile = '/var/log/apache2/modsec_audit.log';

(($fh = fopen($logFile, 'r')) !== false) || exit(vardump("Error", error_get_last()));

while(($line = fgets($fh)) !== false) {
  if(($x = preg_match("~^(--[a-f0-9]+-)A--~", $line, $m)) === 1) {
    $trans = $m[1];

    $flag = false; // Set flag so we get the first line.

    $line = fgets($fh); // The second line has the ip address

    if(preg_match("~(\d+\.\d+\.\d+\.\d+) ~", $line, $m) !== 0) {
      $ip = "{$m[1]}";
    }

    continue;
  }

  if(preg_match("~{$trans}Z~", $line) === 1) {
    if($transaction) {
      $ret .= "$transaction<br>IP: <span class='ip'>$ip</span><br>$matchb<br>$matchf<br>";
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

  if($flag === false && (preg_match("~Total Inbound Score: (\d+).*(SQLI.*)\)~", $line, $m) === 1)) {
    $anomaly = "{$m[1]} {$m[2]}";
    $flag = true; // Don't trigger again until Z.
    continue;
  }

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

    foreach($ar3 as $val) {
      if(preg_match("~$val~", $line) === 1) {
        $tmp[] = $line; // could be Host or User-Agent
        break;
      }
    }

    $line = fgets($fh);
      
    foreach($ar3 as $val) {
      if(preg_match("~$val~", $line) ===1) {
        $tmp[] = $line;
        break;
      }
    }
    
    $matchb1 = $tmp[0];
    $matchb2 = $tmp[1];
    continue;
  }

  if(preg_match("~{$trans}F~", $line) === 1) {
    $line = fgets($fh);
    $matchf = null;

    foreach($ar2 as $key=>$needle) {
      $transaction = null;
      if(preg_match("~$needle~", $line, $m) === 1) {
        if($key == 3) { // The first three are 500, 404 and 200
          $transaction =  "{$trans}A--"; // Set the transaction so it prints at Z
          $matchf = "{$m[1]}";
        }
        break; // break out of foreach and continue to top.
      }
    }
  }
}

$S->title = "Show ModSec Log";
$S->banner = "<h1>$S->title</h1>";

$S->b_inlineScript = <<<EOF
$(".ip").on("click", function(e) {
  const ip =$(this).text();
  console.log("ip: " + ip);
  const sql = `select id,ip,site,page,botAs,finger,nogeo,browser,agent,referer,hex(isjavascript) as java,error,starttime,endtime,difftime,lasttime
 from $S->masterdb.tracker `;

  $.ajax({
    url: './findip.php',
    data: { page: 'find', sql: sql, where: `where ip='${ip}'`, and: 'and lasttime>current_date - interval 5 day', by: 'order by lasttime desc' },
    type: "post",
    success: function(data) {
      let newWindow = window.open("", "_blank"); 

      // Write the response content to the new window
      newWindow.document.write(data); 

      // Close the document stream
      newWindow.document.close(); 
    },
    error: function(err) {
      console.log("SHOW ERROR: ", err);
    }
  });
});
EOF;

$S->css = <<<EOF
.ip { cursor: pointer; }
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

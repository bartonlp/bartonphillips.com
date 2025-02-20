<?php
// Show the modsecurity log file.

$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$ar1 = ["GET .*", "POST .*"];
$ar2 = ["HTTP/1.1 500", "HTTP/1.1 404", "HTTP/1.1 200", "HTTP/1.1 40. .*"];
$flag = false;
$ret = null;

$logFile = '/var/log/apache2/modsec_audit.log';

$fh = fopen($logFile, 'r');
if(!$fh) { vardump("Error", error_get_last()); exit(); }

while(($line = fgets($fh)) !== false) {
  if(($x = preg_match("~^(--[a-f0-9]+-)A--~", $line, $m)) === 1) {
    $trans = $m[1];

    // Get next linel
    
    if(($line = fgets($fh)) === false) {
      echo "Exit";
      exit();
    }

    $capture = true;
    $flag = false;

    if(preg_match("~(\d+\.\d+\.\d+\.\d+) ~", $line, $m) !== 0) {
      $ip = "{$m[1]}";
    }

    continue;
  }

  if(preg_match("~{$trans}Z~", $line) === 1) {
    $capture = false;
    if($transaction) {
      $ret .= "$transaction<br>IP: <span class='ip'>$ip</span><br>$matchb<br>$matchf<br>";
      if(str_contains($matchb1, "Host: 192.241.132.229:443")) {
        $ret .= "<span class='me'>$matchb1</span><br>";
      } else {
        $ret .= "$matchb1<br>";
      }
      $ret .= "$matchb2<br>";
      
      if($anomaly) {
        $ret .= "Score: $anomaly<br>";
      }
      $ret .= "=======================================<br><br>";
    }
    continue;
  }

  if($flag === false && (preg_match("~Total Inbound Score: (\d+).*(SQLI.*)\)~", $line, $m) === 1)) {
    $anomaly = "{$m[1]} {$m[2]}";
    $flag = true;
    continue;
  }

  if($capture) {
    if(preg_match("~{$trans}B~", $line, $m) === 1) {
      $line = fgets($fh);
      foreach($ar1 as $needle) {
        if(preg_match("~$needle~", $line, $m) === 1) {
          $matchb = "Match B: {$m[0]}";
          $line = fgets($fh);
          $matchb1 = $line;
          $line = fgets($fh);
          $matchb2 = $line;
          break;
        }
      }
    }

    if(preg_match("~{$trans}F~", $line) === 1) {
      $line = fgets($fh);
      foreach($ar2 as $key=>$needle) {
        if(preg_match("~$needle~", $line, $m) === 1) {
          if($key == 3) {
            $transaction =  "{$trans}A--";
            $matchf = "Match F: {$m[0]}";
          }
          break;
        }
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
</hr>
$bottom
EOF;

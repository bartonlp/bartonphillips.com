<?php
// Show the modsecurity log file.

$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

$ar1 = ["GET .*", "POST .*"];
$ar2 = ["HTTP/1.1 500", "HTTP/1.1 404", "HTTP/1.1 200", "HTTP/1.1 40. .*"];
$flag = false;

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
      $ip = "IP: {$m[1]}";
    }

    continue;
  }

  if(preg_match("~{$trans}Z~", $line) === 1) {
    $capture = false;
    if($transaction) {
      echo "$transaction<br>$ip<br>$matchb<br>$matchf<br>";
      if(str_contains($matchb1, "Host: 192.241.132.229:443")) {
        echo "<span style='color: red'>$matchb1</span><br>";
      } else {
        echo "$matchb1<br>";
      }
      echo "$matchb2<br>";
      
      if($anomaly) {
        echo "Score: $anomaly<br>";
      }
      echo "=======================================<br><br>";
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

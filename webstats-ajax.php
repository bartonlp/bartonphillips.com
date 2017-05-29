<?php
$_site = require_once(getenv("SITELOAD")."/siteload.php");

// Turn an ip address into a long. This is for the country lookup

function Dot2LongIP($IPaddr) {
  if($IPaddr == "") {
    return 0;
  } else {
    $ips = explode(".", "$IPaddr");
    return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
  }
}

// via file_get_contents('webstats.php?list=<iplist>
// Given a list of ip addresses get a list of countries as $ar[$ip] = $name of country.

if($list = $_POST['list']) {
  $S = new Database($_site);

  $list = json_decode($list);
  $ar = array();

  foreach($list as $ip) {
    $iplong = Dot2LongIP($ip);

    $sql = "select countryLONG from $S->masterdb.ipcountry ".
            "where '$iplong' between ipFROM and ipTO";

    $S->query($sql);
    
    list($name) = $S->fetchrow('num');

    $ar[$ip] = $name;
  }
  echo json_encode($ar);
  exit();
}

// via ajax proxy for curl http://ipinfo.io/<ip>

if($_POST['page'] == 'curl') {
  $ip = $_POST['ip'];

  $cmd = "http://ipinfo.io/$ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));
  $locstr = "Hostname: $loc->hostname<br>$loc->city, $loc->region $loc->postal<br>Location: $loc->loc<br>ISP: $loc->org<br>";

  echo $locstr;
  exit();
}

// via ajax findbot. Search the bots table looking for all the records with ip

if($_POST['page'] == 'findbot') {
  $S = new Database($_site);
  
  $ip = $_POST['ip'];

  $human = [3=>"Robots", 0xc=>"SiteClass", 0x30=>"Sitemap", 0xc0=>"cron"];
  
  $S->query("select agent, who, robots from barton.bots where ip='$ip'");

  $ret = '';

  while(list($agent, $who, $robots) = $S->fetchrow('num')) {
    $h = '';
    
    foreach($human as $k=>$v) {
      $h .= $robots & $k ? "$v " : '';
    }

    $bot = sprintf("%X", $robots);
    $ret .= "<tr><td>$who</td><td>$agent</td><td>$bot</td><td>$h</td></tr>";
  }

  if(empty($ret)) {
    $ret = "<div style='background-color: pink; padding: 10px'>$ip Not In Bots</div>";
  } else {
    $ret = <<<EOF
<style>
#FindBot table {
  width: 100%;
}
#FindBot table td:first-child {
  width: 20%;
}
#FindBot table td:nth-child(2) {
  word-break: break-all;
  width: 70%;
}
#FindBot table td:nth-child(3) {
  width: 10%;
}
#FindBot table * {
  border: 1px solid black;
}
</style>
<table>
<thead>
  <tr><th>$ip</th><th>Agent</th><th>Bots</th><th>Human</th></tr>
</thead>
<tbody>
$ret
</tbody>
</table>
EOF;
  }
  echo $ret; 
  exit();
}

// AJAX from webstats-new.js
// Get the info form the tracker table again.
// NOTE this is called from js/webstats-new.js which always uses this file for its AJAX calls!!

if($_POST['page'] == 'gettracker') {
  $S = new Database($_site);
  
  // Callback function for maketable()

  function callback1(&$row, &$desc) {
    global $S, $ipcountry;

    $ip = $S->escape($row['ip']);

    $co = $ipcountry[$ip];

    $row['ip'] = "<span class='co-ip'>$ip</span><br><div class='country'>$co</div>";

    console.log("js: " + $row['js']);
    
    if(($row['js'] & 0x2000) === 0x2000) {
      $desc = preg_replace("~<tr>~", "<tr class='bots'>", $desc);
    }
    $row['js'] = dechex($row['js']);
    $t = $row['difftime'];
    if(empty($t)) return;
    
    $hr = floor($t/3600);
    $min = floor(($t%3600)/60);
    $sec = ($t%3600)%60;
      //echo "$ip, t=$t, hr: $hr, min: $min, sec: $sec<br>";
    $row['difftime'] = sprintf("%u:%02u:%02u", $hr, $min, $sec);
  } // End callback

  $site = $_POST['site'];
  
  $ipcountry = json_decode($_POST['ipcountry'], true);

  $T = new dbTables($S);

  $sql = "select ip, page, agent, starttime, endtime, difftime, isJavaScript as js, refid ".
         "from $S->masterdb.tracker " .
         "where site='$site' and starttime >= current_date() - interval 24 hour ". 
         "order by starttime desc";

  list($tracker) = $T->maketable($sql, array('callback'=>callback1,
                                             'attr'=>array('id'=>'tracker', 'border'=>'1')));

  echo $tracker;
  exit();
}


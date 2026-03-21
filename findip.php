<?php
// BLP 2024-12-15 - 
// Given the ip find the records in tracker, geo, bots.
// Given a 'where' clause.
// Given an 'and' clause.
/*
CREATE TABLE `tracker` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `botAs` varchar(30) DEFAULT NULL,
  `botAsBits int DEFAULT 0,
  `site` varchar(25) DEFAULT NULL,
  `page` varchar(255) NOT NULL DEFAULT '',
  `finger` varchar(50) DEFAULT NULL,
  `nogeo` tinyint(1) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `count` int DEFAULT 1,
  `browser` varchar(50) DEFAULT NULL,
  `agent` text,
  `referer` varchar(255) DEVAULT '',
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `difftime` varchar(20) DEFAULT NULL,
  `isJavaScript` int DEFAULT '0',
  `error` varchar(256) DEFAULT NULL,
  `lasttime` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `site` (`site`),
  KEY `ip` (`ip`),
  KEY `lasttime` (`lasttime`),
  KEY `starttime` (`starttime`)
) ENGINE=MyISAM AUTO_INCREMENT=6242964 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `bots3` (
  `ip` varchar(50) NOT NULL COMMENT 'big enough to handle IP6',
  `agent` text NOT NULL COMMENT 'big enough to handle anything',
  `count` int DEFAULT '1' COMMENT 'the number of time this has been updated',
  `robots` int DEFAULT NULL COMMENT 'bit mapped values as above see defines.php',
  `site` int DEFAULT NULL COMMENT 'bitmasked values of sites see defines.php',
  `page` varchar(255) DEFAULT NULL COMMENT 'the page on my site',
  `created` datetime DEFAULT NULL COMMENT 'when record created',
  `lasttime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'auto, the lasttime this was updated',
  UNIQUE KEY `ip_agent_page` (`ip`,`agent`(255),`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `geo` (
  `lat` varchar(50) NOT NULL,
  `lon` varchar(50) NOT NULL,
  `finger` varchar(100) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

$_site = require_once(getenv("SITELOADNAME"));
//$_site = require_once "/var/www/site-class/includes/autoload.php";
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);
$T = New dbTables($S);

$val = "select id, ip, site, page, hex(botAsBits) as botAsBits, finger, nogeo, browser, agent, referer, count, hex(isjavascript) as java, ".
       "error, starttime, endtime, difftime, lasttime ".
       "from $S->masterdb.tracker ";

$getinfo = function($value=null, $sql) use ($S, $T, &$fingers, &$sqlval, &$ip, &$id, &$agent, &$page) {
  $sql = "{$sql} {$value}";
  $sqlval = $sql; // global used in first line of render.

  // Callback for tracker
  // uses the makeresultrow method because maketable is not set to true.
  
  $trackerCallback = function(&$cellStr, &$row) use (&$fingers, &$ip, &$agent, &$page) {
    // Do this only once. When $ip is set so are $agent and $page.
    
    if(is_null($ip)) { // If the 'where' clause had ip='...' then ip is already set!
      $ip = $row['ip'];
      //if(preg_match("~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~", $ip) === 0) logInfo("findip.php callback: No ip=$ip");
    }

    // Do these only if they are not set.
    
    $agent = $agent ?? $row['agent'];
    $page = $page ?? $row['page']; 
          
    $ref = basename($row['referer']);

    $cellStr = preg_replace_callback("~(<td class=.*?referer.*?>).*?</td>~", function($m) use ($ref) {
      return "$m[1] $ref</td>";
    }, $cellStr);
    
    if($row['finger'] != '') {
      $fingers[] = $row['finger'];
    }
    
    $t = $row['difftime'];

    $hr = $t/3600;
    $min = ($t%3600)/60;
    $sec = ($t%3600)%60;
    $time = sprintf("%u:%02u:%02u", $hr, $min, $sec);

    $cellStr = preg_replace_callback("~(<td class=.*?difftime.*?>).*?</td>~", function($m) use ($time) {
      return "$m[1] $time</td>";
    }, $cellStr);
  };

  // Create tracker table. Use new makerow method. Add true as third argument.
  
  $trackerTbl = $T->maketable($sql,
                              ['callback'=>$trackerCallback,
                               'attr'=>['id'=>'trackertbl', 'border'=>'1']], true)[0];

  // If $trackerTbl is empty then there is NO ip and nothing will work.
  
  if(empty($trackerTbl)) {
    $trackerTbl = "<h1>Not in tracker table</h1>";
    logInfo("findip \$trackerTbl empty: $value, line=". __LINE__);
    $botsTbl = $locstr = $geoTbl = $badTbl = $ip2info = null;
  } else {
    // Good id so we can press on with the $ip and $finger form trackerCallback().
    
    $trackerTbl = "<h1>From tracker table</h1>$trackerTbl";
    
    // $fingers is from the trackerCallback().
    // NOTE: The $fingers array only has the 'and' clause info form the tracker table. So if the
    // 'and' clause has a date restriction like 'lasttime>now() - interval 5 day' you will not see
    // all of the goe information.

    foreach($fingers as $val) {
      $vals .= "'$val',";
    }
    $vals = rtrim($vals, ','); // BLP 2025-01-30 - remove trailing comma.

    //  Create the bots3 tables

    $sql = "select ip, agent, page, count, hex(robots) as robots, hex(site) as site, created, lasttime ".
           "from $S->masterdb.bots3 where ip='$ip' and agent='$agent' and page='$page' order by lasttime desc";

    $botsTbl = $T->maketable($sql, ['attr'=>['id'=>'botstbl', 'border'=>'1']], true)[0];

    $botsTbl = empty($botsTbl) ? "<h1>Not in bots3 table for 'ip', 'agent', 'page'</h1>" : "<h1>From bots3 table for 'ip', 'agent', 'page'</h1>$botsTbl";

    $sql = "select ip, agent, page, count, hex(robots) as robots, hex(site) as site, created, lasttime ".
           "from $S->masterdb.bots3 where ip='$ip' order by lasttime desc";
    
    $botsIpTbl = $T->maketable($sql, ['attr'=>['id'=>'botsiptbl', 'border'=>'1']], true)[0];

    $botsIpTbl = empty($botsIpTbl) ? "<h1>Not in bots3 table for 'ip'</h1>" : "<h1>From bots3 table for 'ip'</h1>$botsIpTbl";

    // Create the geo table. $vals is a string created from the $fingers array. This is from trackerCallback().

    if($vals) {
      $sql = "select lat, lon, finger, site, created, lasttime from $S->masterdb.geo where finger in($vals) order by lasttime desc";
      $geoTbl = $T->maketable($sql, ['attr'=>['id'=>'mygeo', 'border'=>'1']], true)[0];
    }

    $geoTbl = empty($geoTbl) ? "<h1>Not in geo table</h1>" : "<p>From geo table</p>$geoTbl";

    // Create the $locstr from ipinfo.io.

    if(preg_match("~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~", $ip) === 1) { // A real ip
      $loc = json_decode(file_get_contents("http://ipinfo.io/$ip")); // I could also use the class link in webstats.js
      $gpsloc = $loc->loc;
      $hostby = gethostbyaddr($ip);
      $locstr = <<<EOF
<p>User Information from "http://ipinfo.io/$ip"</p>
<ul class="user-info">
  <li>gethostbyaddr: <i class='green'>$hostby</i></li>
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li id="location">GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
 </ul>
EOF;

      // Create additional info from api.ip2location.io

      $key = require '/var/www/PASSWORDS/Ip2Location-key';
      $bigdatakey = require '/var/www/PASSWORDS/BigDataCloudAPI-key';

      // If this fails it will throw an exception
      
      $json = file_get_contents("https://api.ip2location.io/?key=$key&ip=$ip");

      $info = json_decode($json);

      $proxy = $info->is_proxy ? "true" : "false";

      $ip2info = <<<EOF
<table id="results" border='1'>
<tr><td>IP Address</td><td>$ip</td></tr>
<tr><td>Country Code</td><td>$info->country_code</td></tr>
<tr><td>Country Name</td><td>$info->country_name</td></tr>
<tr><td>Reagion</td><td>$info->region_name</td></tr>
<tr><td>City</td><td>$info->city_name</td></tr>
<tr><td>Zip Code</td><td>$info->zip_code</td></tr>
<tr><td>Time Zone</td><td>$info->time_zone</td></tr>
<tr><td>Autonomous System Number</td><td>AS$info->asn</td></tr>
<tr><td>Autonomous System</td><td>$info->as</td></tr>
<tr><td>Proxy</td><td>$proxy</td></tr>
EOF;
      // Get info from api-bdc.net
      // If file_get_contents fails it throws and exception.
      
      $json = file_get_contents("https://api-bdc.net/data/hazard-report?ip=$ip&key=$bigdatakey");

      $istor = json_decode($json);
      //vardump("findip: istor", $istor);

      $tordisp = null;

      foreach($istor as $k=>$v) { // The informaton is in key value pairs.
        if($v === 0 || !empty($v)) {
          $tordisp .= "<tr><td>$k</td><td>$v</td></tr>";
        }
      }

      $ip2info .= empty($tordisp) ? "<tr><td colspan='2'>No api-bdc.net Info</td></tr>" : $tordisp;

      // Get the RiskLevel from api-bdc.net

      $json = file_get_contents("https://api-bdc.net/data/user-risk?ip=$ip&key=$bigdatakey");

      $istor = json_decode($json);

      $ip2info .= empty($istor->description) ? "<tr><td colspan='2'>No RiskLevel found</td></tr></table>" :
                  "<tr><td>RiskLevel</td><td>$istor->description</td></tr></table>";

      $sql = "select ip, hex(botAsBits) as botAsBits, type, errno, errmsg, agent, created, lasttime ".
             "from $S->masterdb.badplayer where ip='$ip' order by lasttime";

      $badTbl = $T->maketable($sql, ['attr'=>['id'=>'badplayer', 'border'=>'1']], true)[0];
    } else {
      logInfo("findip: No ip ($ip), id=$id");
      $locstr = "<h1>No Location Data Available from <i>http://ipinfo.io</i></h1>";
    }

    $badTbl = empty($badTbl) ? "<h1>Not in badplayer table</h1>" : "<h1>From badplayer table</h1>$badTbl";
  }

  return [$trackerTbl, $botsTbl, $botsIpTbl, $locstr, $geoTbl, $badTbl, $ip2info];
};

// ********************************
// Start Here
// ********************************

if($_POST['page'] == 'find') { // 'find' has two modes one with 'type' the other without.
  // If a POST

  $where = $_POST['where'];
  preg_match("~(?:(ip)='(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})'|(id)='(\d+)')~", $where, $m);
  logInfo("**findip: post ". print_r($m, true));
  if($m[1] == 'ip') {
    $searchIp = $m[2];
  } else {
    $id = $m[4];
  }
  logInfo("**findip: post id=$id");

  $and = $_POST['and'];
  $by = $_POST['by'];
  $value = "$where $and $by";
  $sql = $_POST['sql'] ?? $val;

  //logInfo("where=$where, and=$and, by=$by, sql=$sql");
  
  // If type is 'post' just get the raw array and echo it back to the AJAX caller.
  
  if($_POST['type'] == 'post') {
    $data = json_encode($getinfo($value, $sql));
    echo $data;
    exit();
  }
  // FROM a <form>
  // If type does not equal 'post' fall through. We can do this because this post was from a <form>.
} elseif($_GET) {
  // If a GET. We send the information in data as json.

  $tmp = $_GET['data'];
  $data = json_decode($tmp, true);

  // If called from showmodsec.php

  if($data['message']) {
    $data = $data['message'];
  }

  // Now use the information in data. data[0] is 'where', data[1] is 'and' data[3] is 'by'.
  
  $where = $data[0];
  logInfo("**findip: where=|$where|");

  preg_match("~(?:(ip)='(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})'|(id)='(\d+)')~", $where, $m);
  logInfo("**findip: ". print_r($m, true));
  if($m[1] == 'ip') {
    $searchIp = $m[2];
  } else {
    $id = $m[4];
  }
  logInfo("**findip: id=$id");
  $and = $data[1];
  $by = $data[2];
  if(!str_contains($by, 'order')) {
    logInfo("findip.php GET, 'by' error: id=$S->LAST_ID, ip=$S->ip, site=$S->site, page=$S->self, agent=$S->agent");
    exit('Go Away');
  }
  
  $value = "$where $and $by";
  $sql = $val;
}

$ip = $searchIp;

if(!empty($sql)) {
  [$trackerTbl, $botsTbl, $botsIpTbl, $locstr, $geoTbl, $badTbl, $ip2info] = $getinfo($value, $sql);
  $ip = $searchIp;
} else {
  $sql = $val;
  $where = "where ip='195.252.232.86'";
  $and = "and lasttime>current_date()";
  $by = "order by lasttime desc";
}

$S->banner = "<h1>Find in Tracker</h1>";
$S->title = "Find in Tracker";

// BLP 2024-12-15 - Start
$ref = $_SERVER['HTTP_REFERER'];

if(!str_contains($ref, "bartonphillips.com")) {
  echo <<<EOF
<h1>Authorization Denied</h1>
<p>Try <a href="https://bartonphillips.com/findip.php">findip.php</a>.</p>
EOF;
  $requestUri = urldecode($S->requestUri);

  $errMsg = 'Not called from bartonphillips.com';
  
  if(preg_match("~(?:ip='(.*?)')|(?:id='(.*?)')~", $requestUri, $m) !== false) {
    if(!empty($m[1])) {
      $x = "ip='$m[1]'";
    } elseif(!empty($m[2])) {
      $x = "id=$m[2]";
    } else {
      $ip = $S->ip;
      $id = 999;
      $created = date("Y-m-d H:i:s");
      goto SKIP_SELECT;
    }

    $S->sql("select id, ip, site, page, botAsBits, agent, starttime from $S->masterdb.tracker where $x");
    [$id, $ip, $site, $page, $botAsBits, $agent, $created] = $S->fetchrow('num'); 

SKIP_SELECT:

    if(!$ip) {
      $ip = $searchIp;
      $errMsg .= ": NO IP from tracker table, using \$searchIp=$ip";
    }
    if(!$id) $id = "NO ID from tracker table";

    // In this table id is a varchar(30)
    
    $S->sql("insert into $S->masterdb.badplayer (ip, id, site, page, botAsBits, type, errno, errmsg, agent, created, lasttime) ".
            "values('$ip', '$id', '$site', '$page', $botAsBits, 'AUTHORIZATION DENIED', -999, '$errMsg', '$agent', '$created', now())");

    logInfo("findip.php: $errMsg, id=$id, ip=$ip, site=$site, page=$page, botAsBits=$botAsBits, agent=$agent, requestUri=$requestUri");
  } else {
    logInfo("findip.php: preg_match() returned false. ERROR");
  }
  exit();
}
// BLP 2024-12-15 - End

// CSS

$S->css =<<<EOF
/* 1 is id, 2 is ip address */
/*#trackertbl td:nth-of-type(1), #trackertbl td:nth-of-type(2) {
  cursor: pointer;
}*/
.id, .ip { cursor: pointer; }
/* 4 is page*/
/*#trackertbl td:nth-of-type(4) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}*/
.page { overflow-x: auto; max-width: 100px; white-space: pre; cursor: pointer; }
/* 5 is botAsBits */
/*#trackertbl td:nth-of-type(5) {
  max-width: 20px;
  cursor: pointer;
}*/
.botAsBits { max-width: 20px; cursor: pointer; }
/* 6 is finger */
/*#trackertbl td:nth-of-type(6) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}*/
.finger { overflow-x: auto; max-width: 100px; white-space: pre; cursor: pointer; }
/* 9 is agent */
/*#trackertbl td:nth-of-type(9) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}*/
.agent { overflow-x: auto; max-width: 100px; white-space: pre; cursor: pointer; }
/* 10 is referer */
/*#trackertbl td:nth-of-type(10) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}*/
.referer { overflow-x: auto; max-width: 100px; white-space: pre; cursor: pointer; }
/* 12 is javascript */
/*#trackertbl td:nth-of-type(12) { cursor: pointer;}*/
.java { cursor: pointer; }
/* 13 is error */
/*#trackertbl td:nth-of-type(13) {
  overflow-x: auto;
  max-width: 100px;
  white-space: pre;
  cursor: pointer;
}*/
.error { overflow-x: auto; max-width: 100px; white-space: pre; cursor: pointer; }

#trackerContainer, #botsContainer, #mygeo, #badplayer, #daycounts {
  width: 100%;
}
#trackertbl { position: relative; font-size: 16px; width: 100%; }
#trackerContainer td { padding-left: 10px; padding-right: 10px; }
#botsContainer td { padding-left: 10px; padding-right: 10px; }
/* agent field */
#botsContainer td:nth-of-type(2) { word-break: break-all; width: 900px; }
#botstbl, #botsiptbl, #mygeo, #dayrecords, #badplayer { width: 100%; }
#botstbl, #botsiptbl { position: relative; }
#botstbl td:nth-of-type(5), #botstbl td:nth-of-type(6),
#botsiptbl td:nth-of-type(5), #botsiptbl td:nth-of-type(6) { cursor: pointer; }
#badplayer td { padding-left: 10px; padding-right: 10px; font-size: 18px;}
#form th { text-align: left; padding-right: 10px; font-size: 18px; }
#locationx { font-size: 18px; }
input { width: 1000px; font-size: 18px; padding-right: 10px; padding-left: 10px; }
button { border-radius: 5px; background: green; color: white; font-size: 18px; }
/* mygeo is the table */
#mygeo td {
  cursor: pointer;
}
#mygeo th {
  width: 220px;
}
#mygeo td { padding-left: 10px; padding-right: 10px; }
/* geo is the div for the google maps image */
#geocontainer {
  width: 100%;
  height: 99%;
  margin-right: auto;
  border: 5px solid black;
  z-index: 100;
}
#removemsg {
  color: white;
  background: red;
  border-radius: 5px;
  padding: 4px;
  margin: 2px 0 2px 2px;
  z-index: 100;
}
#outer {
  display: none;
  position: absolute;
  background: white;
  z-index: 99;
  padding-bottom: 30px;
  border: 5px solid red;
}
#location i { cursor: pointer; }
.green { color: green; }
.botas { color: green; }
/* BLP 2024-07-17 - MORE */
#ip2info { margin-top: 5px; margin-bottom: 20px; }
#ip2info td { padding: 5px; }
EOF;

$S->cssLink = "findip.css";

// setupjava.i.php puts the information for the JavaScript used in the popup for the isJavaScript
// human information. It goes into h_inlineScript. If we add anything else to h_inlineScript it
// must ust .= 

require_once("/var/www/bartonlp.com/otherpages/setupjava.i.php"); // added to h_inlineScript.

// BLP 2025-03-06 - add tablesort. newtblsort.css does not change the header css.
// tablesorter-master/dist/css/theme.blue.min.css does.

$S->link = <<<EOF
  <link rel="stylesheet" href="https://bartonphillips.net/css/newtblsort.css">
EOF;

// BLP 2025-03-06 - tablesorter is the most recent version 2.32.0

$S->h_script = "<script src='https://bartonphillips.net/tablesorter-master/dist/js/jquery.tablesorter.min.js'></script>";

$S->b_script =<<<EOF
<script src="https://bartonphillips.net/js/jquery.mobile.custom.js"></script>
<!-- UI for drag and drop and touch-punch for mobile drag -->
<script src="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery-ui.js"></script>
<script src="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery.ui.touch-punch.js"></script>
<link rel="stylesheet" href="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery-ui.css">
<script src="https://bartonphillips.net/js/maps.js"></script>
<script
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6GtUwyWp3wnFH1iNkvdO9EO6ClRr_pWo&callback=initMap&v=weekly" async>
</script>
EOF;

// setupjava.i.php puts the information for the JavaScript used in the popup for the isJavaScript
// human information. It goes into h_inlineScript. If we add anything else to h_inlineScript it
// must ust .= 

require_once("/var/www/bartonlp.com/otherpages/setupjava.i.php"); // added to h_inlineScript.

// BLP 2025-03-06 - add tablesort. newtblsort.css does not change the header css.
// tablesorter-master/dist/css/theme.blue.min.css does.

$S->link = <<<EOF
  <link rel="stylesheet" href="https://bartonphillips.net/css/newtblsort.css">
EOF;

// BLP 2025-03-06 - tablesorter is the most recent version 2.32.0

$S->h_script = "<script src='https://bartonphillips.net/tablesorter-master/dist/js/jquery.tablesorter.min.js'></script>";

$S->b_script =<<<EOF
<script src="https://bartonphillips.net/js/jquery.mobile.custom.js"></script>
<!-- UI for drag and drop and touch-punch for mobile drag -->
<script src="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery-ui.js"></script>
<script src="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery.ui.touch-punch.js"></script>
<link rel="stylesheet" href="https://bartonphillips.net/js/jquery-ui-1.13.0.custom/jquery-ui.css">
<script src="https://bartonphillips.net/js/maps.js"></script>
<script
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6GtUwyWp3wnFH1iNkvdO9EO6ClRr_pWo&callback=initMap&v=weekly" async>
</script>
EOF;

$S->noCounter = true; // No counter.

$S->b_inlineScript =<<<EOF
  function removeAll() {
    $("#FindBot").remove();
  }

  $(document).on('click', function(e) {
    if(!$(e.target).closest('#outer').length) {
      $('#outer').hide(); // Hide the element
    }
  });

  // Set up tablesorter

  let headers = {};
    
  // Disable all columns except 13 and 16 (starttime, lasttime). Note tablesorter is zero based.

  for(let i = 0; i < 16; i++) {
    if(i !== 13 && i !== 16) {
      headers[i] = { sorter: false };
    }
  }

  $("#trackertbl").tablesorter({theme: 'blue', headers: headers});

  // td 1 is the ID, td 2 is IP.

  $("body").on("click", ".id, .ip", function(e) {
    const idOrIp = $(this).text();
    const cl = e.currentTarget.className;
  
    const where = "where " +cl+"='" +idOrIp+ "'";
    const and = "and lasttime>current_date() -interval 5 day";
    const by = "order by lasttime desc";

    const data = JSON.stringify([where, and, by]); 

    // Here we set type to 'post' which causes the POST to return the raw data from getinfo()

    $.ajax({
      url: "/findip.php",
      data: {page: "find", type: "post", where: where, and: and, by: by},
      type: "post",
      success: function(value) {
        value = JSON.parse(value);
        console.log("DATA: ", value);
        //$("#trackerContainer").html("<p>This is new</p>" + value[0]);
        location.replace("findip.php?data=" + data);
      },
      error: function(err) {
        console.log("ERROR: ", err);
      }
    });
  });

  // These tds each have cursor: pointer.
  // If td 4=page, 6=finger, 9=agent, 10=referer and 13=error. 
  // When clicked show the whole cell item.
  // ALSO if Ctrl Key is pressed on td 9 (agent) we open a new tab with the agents bot information
  // website.

  $("body").on("click", "#trackertbl td:nth-of-type(4), "+
                        "#trackertbl td:nth-of-type(6), #trackertbl td:nth-of-type(9), "+
                        "#trackertbl td:nth-of-type(10), #trackertbl td:nth-of-type(13)",
                        function(e) {
    // A ctrl key and cellIndex 7 which is td 8 (agent).

    if(e.ctrlKey && $(this)[0].cellIndex == 8) {
      const txt = $(this).text();
      const pat = /(https?:\/\/.*?)(?:\)|;)/; // The text must start with http etc.
      const found = txt.match(pat);

      if(found) {
        // open the window in a new tab.

        window.open(found[1], "bot");
      } else {
        $(this).css({color: 'red'});
      }

      e.stopPropagation();
    } else {
      // Note ctrl key and cellIndex 7
      // Just a normal click.

      let ypos, xpos;
      let pos = $(this).position();
      xpos = pos.left - 200;
      ypos = pos.top + 30;

      removeAll();

      $("#trackertbl").append("<div id='FindBot' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                              "background-color: white; border: 5px solid black; "+
                              "padding: 10px;'>"+$(this).text()+"</div>");
      e.stopPropagation();
    }
  });

  // trackertbl td 5 is botAsBits
  // trackertbl td 12 is the java script value.
  // botstbl td 5 is the robots value
  // botstbl td 6 is the site value
  // botsiptbl td 5 is the robots value
  // botsiptbl td 6 is the site value
  // Show the human readable values.

  $("body").on("click", `#trackertbl td:nth-of-type(5), #trackertbl td:nth-of-type(12), 
#botstbl td:nth-of-type(5), #botstbl td:nth-of-type(6),
#botsiptbl td:nth-of-type(5), #botsiptbl td:nth-of-type(6)`, function(e) {
    let js = parseInt($(this).text(), 16),
    h = '', ypos, xpos;
    let human;

    // Make it look like a hex. Then 'and' it with 0x100 if it is true
    // then make js 0x1..
        
    let table = $(this).closest("table");
    let pos = $(this).position(); // get the top and left
    
    // The td is in a tr which in in a tbody, so table is three
    // prents up.

    if(table.attr("id") != 'trackertbl') {
      // Robots (bots table)

      const tdIndex = $(this).index();
      if(tdIndex == 4) {
        // robots
        human = robots; // robots was set in webstats.php in the inlineScript.
      } else if(tdIndex == 5) {
        // site
        const tmp = {
             'bartonphillips.com': 1,
             'bartonphillips.net': 2,
             'bartonlp.com': 4,
             'bartonlp.org': 8,
             'bonnieburch.com': 0x10,
             'newbernzig.com': 0x20,
             'newbern-nc.info': 0x40,
             'jt-lawnservice.com': 0x80,
             'swam.us': 0x100,
             'NO_SITE': 0x10000
        };
        human = Object.fromEntries(
          Object.entries(tmp).map(([domain, bit]) => [bit, domain])
        );
      }
     
      xpos = pos.left + $(this).width() - 180; // add the one border and one padding (15px) plus a mig.
    } else {
      // Tracker table.

      const tdIndex = $(this).index();
      if(tdIndex === 4) {
        // this is human = robots
        human = robots;
      } else if(tdIndex == 11) {
        // this is human = tracker
        human = tracker; // tracker was set in webstats.php in the inlineScript
      }
      xpos = pos.left - 300; // Push this to the left so it will render full size
    }
    ypos = pos.top + 30;

    //console.log("human:", human);

    for(let [k, v] of Object.entries(human)) {
      h += (js & +k) ? v + "<br>" : ''; // +k forces a string to a number.
    }

    removeAll();

    // Now append FindBot to the table.
    
    table.append("<div id='FindBot' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                 "background-color: white; z-index: 1000; border: 5px solid black; "+
                 "padding: 10px;'>"+h+"</div>");

    if(table.attr("id") == "trackertbl") {
      // For tracker recalculate the xpos based on the size of the
      // FindBot item.
      
      xpos = pos.left - ($("#FindBot").width() + 35); // we add the border and padding (30px) plus a mig.
      $("#FindBot").css("left", xpos + "px");
    }
    
    e.stopPropagation();
  });

  $("body").on("click", function(e) {
    removeAll();
  });
EOF;

[$top, $footer] = $S->getPageTopBottom();

$sqlStatment = $sqlval ? "<p>Sql Statment:<br>$sqlval</p>" : null;

// Note, the <form does not set 'type' so the POST falls through to this page.

echo <<<EOF
$top
$sqlStatment
<p>Select your query fields</p>
<form method='post' action="./findip.php">
<table id='form'>
<tbody>
<tr><th>Enter Fields</th><td><input type="text" name="sql" value="$sql"></td></tr>
<tr><th>Where</th><td><input type="text" name="where" value="$where"></td></tr>
<tr><th>and</th><td><input type="text" name="and" value="$and"></td></tr>
<tr><th>Order By</th><td><input type="text" name="by" value="$by"></td></tr>
</tbody>
</table>
<button type='submit' name='page' value='find'>Find</button>
</form>
<div id='locationx'>$locstr</div>
<div id='ip2info'>$ip2info</div>
<div id='trackerContainer'>$trackerTbl</div>
<div id='botsContainer'>
$botsTbl<br>
$botsIpTbl
</div>
<div id='geo'>$geoTbl</div>
<div id='badContainer'>$badTbl</div>
<div id="outer">
  <div id="geocontainer"></div>
  <button id="removemsg">Click to remove map image</button>
</div>
$footer
EOF;

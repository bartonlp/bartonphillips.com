<?php
// BLP 2024-12-15 - 
// Given the ip find the records in tracker, geo, bots.
// Given a 'where' clause.
// Given an 'and' clause.
/*
CREATE TABLE `tracker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botAs` varchar(30) DEFAULT NULL,
  `site` varchar(25) DEFAULT NULL,
  `page` varchar(255) NOT NULL DEFAULT '',
  `finger` varchar(50) DEFAULT NULL,
  `nogeo` tinyint(1) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `agent` text,
  `referer` varchar(255) DEVAULT '',
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `difftime` varchar(20) DEFAULT NULL,
  `isJavaScript` int DEFAULT '0',
  `error` varchar(256) DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site` (`site`),
  KEY `ip` (`ip`),
  KEY `lasttime` (`lasttime`),
  KEY `starttime` (`starttime`)
) ENGINE=MyISAM AUTO_INCREMENT=6242964 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `bots` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `agent` text NOT NULL,
  `count` int DEFAULT NULL,
  `robots` int DEFAULT '0',
  `site` varchar(255) DEFAULT NULL,
  `creation_time` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`,`agent`(254))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `geo` (
  `lat` varchar(50) NOT NULL,
  `lon` varchar(50) NOT NULL,
  `finger` varchar(100) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `badplayer` (
  `ip` varchar(20) NOT NULL,
  `botAs` varchar(50) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `count` int DEFAULT NULL,
  `errno` int DEFAULT NULL,
  `errmsg` varchar(255) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

//$_site = require_once(getenv("SITELOADNAME"));
$_site = require_once "/var/www/site-class/includes/autoload.php";
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

// BLP 2024-12-15 - Start
$ref = $_SERVER['HTTP_REFERER'];

// BLP 2024-12-15 - Start
$ref = $_SERVER['HTTP_REFERER'];

if(!str_contains($ref, "bartonphillips.com")) {
  echo <<<EOF
<h1>Authorization Denied</h1>
<p>Try <a href="https://bartonphillips.com/findip.php">findip.php</a>.</p>
EOF;
  $requestUri = urldecode($S->requestUri);

  $errMsg = 'Not called from showErrorLog.php';
  
  if(preg_match("~(?:ip='(.*?)')|(?:id='(.*?)')~", $requestUri, $m) !== false) {
    if(!empty($m[1])) {
      $x = "ip='$m[1]'";
    } elseif(!empty($m[2])) {
      $x = "id=$m[2]";
    } else {
      $errMsg = "Not called from index.php";
      $ip = $S->ip;
      $id = 999;
      $created = date("Y-m-d H:i:s");
      goto SKIP_SELECT;
    }

    $S->sql("select id, ip, site, page, botAs, agent, starttime from $S->masterdb.tracker where $x");
    [$id, $ip, $site, $page, $botAs, $agent, $created] = $S->fetchrow('num');

SKIP_SELECT:
    
    $S->sql("insert into $S->masterdb.badplayer (ip, id, site, page, botAs, type, count, errno, errmsg, agent, created, lasttime) ".
            "values('$ip', $id, '$site', '$page', '$botAs', 'AUTHORIZATION DENIED', 1, -999, '$errMsg', '$agent', '$created', now()) ".
            "on duplicate key update count=count+1, lasttime=now()");

    error_log("findip.php: $errMsg, id=$id, ip=$ip, site=$site, page=$page, botAs=$botAs, agent=$agent, requestUri=$requestUri");
  } else {
    error_log("findip.php: preg_match() returned false. ERROR");
  }
  exit();
}
// BLP 2024-12-15 - End

$T = new dbTables($S);

$val = "select id,ip,site,page,botAs,finger,nogeo,browser,agent,referer,hex(isjavascript) as java,error,starttime,endtime,difftime,lasttime ".
       "from $S->masterdb.tracker ";

// These MUST BE TRUE globals!!
$fingers = [];
$ip = null;

function getinfo($value=null, $sql) {
  global $S, $T, $fingers, $sqlval;

  $sql = "{$sql} {$value}";
  $sqlval = $sql; // global used in first line of render.
  
  $ip = null;
  
  function trackerCallback(&$row, &$desc) {
    global $fingers, $ip; // these are true globals, outside of getinfo().

    if(is_null($ip)) {
      $ip = $row['ip']; // Do this once. Get ip from tracker table
    }

    $row['referer'] = basename($row['referer']);
    
    if($row['finger'] != '') {
      $fingers[$row['finger']]++;
    }
    $t = $row['difftime'];
    $hr = $t/3600;
    $min = ($t%3600)/60;
    $sec = ($t%3600)%60;
    $row['difftime'] = sprintf("%u:%02u:%02u", $hr, $min, $sec);
  }

  $trackerTbl = $T->maketable($sql, ['callback'=>'trackerCallback', 'attr'=>['id'=>'trackertbl', 'border'=>'1']])[0];

  $trackerTbl = $trackerTbl ?? "<h1>Not in tracker table</h1>";

  foreach(array_keys($fingers) as $key) {
    $keys .= "'$key',";
  }
  $keys = rtrim($keys, ',');

  // If we are using ip use it, if we are doing id set the $bitsWhere to the ip we got in the
  // callback.

  if(preg_match("~ip=['\"](.*)['\"]~", $value, $m)) {
    $ip = $m[1];
    $botsWhere = "where ip='$ip'"; // This is the full phrase (via the global)
  
    // At this point $ip is either the ip form the $where or the ip from the callback.
  
    $sql = "select ip, agent, count, hex(robots) as robots, site, creation_time, lasttime from $S->masterdb.bots $botsWhere order by lasttime";

    $botsTbl = $T->maketable($sql, ['attr'=>['id'=>'botstbl', 'border'=>'1']])[0];
  
    if($botsTbl) {
      $botsTbl = "<p>From bots table</p>$botsTbl";
    } else {
      $botsTbl = "<h1>Not in bots table</h1>";
    }
  }

  $sql = "select lat, lon, finger, site, created, lasttime from $S->masterdb.geo where finger in($keys) order by lasttime";

  if($keys) {
    $geoTbl = $T->maketable($sql, ['attr'=>['id'=>'mygeo', 'border'=>'1']])[0];
  }
  if($geoTbl) {
    $geoTbl = "<p>From geo table</p>$geoTbl";
  } else {
    $geoTbl = "<h1>Not in geo table</h1>";
  }

  if($ip) {
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
  } else {
    $locstr = "<h1>No Location Data Available from <i>http://ipinfo.io</i></h1>";
  }

  if(!empty($ip)) {
    $sql = "select ip, botAs, type, count, errno, errmsg, agent, created, lasttime from $S->masterdb.badplayer where ip='$ip'";
    $badTbl = $T->maketable($sql, ['attr'=>['id'=>'badplayer', 'border'=>'1']])[0];
  }
  if($badTbl) {
    $badTbl = "<p>From badplayer table</p>$badTbl";
  } else {
    $badTbl = "<h1>Not in badplayer table</h1>";
  }

  // BLP 2024-07-17 - add more info
  
  $key = require '/var/www/PASSWORDS/Ip2Location-key';
  $bigdatakey = require '/var/www/PASSWORDS/BigDataCloudAPI-key';
  
  $ipin = $_POST['ip'];
  if(($json = file_get_contents("https://api.ip2location.io/?key=$key&ip=$ip")) === false) exit("<h1>Not a Valid IP</h1><p>ip2location failed</p>");
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
  
  if(($json = file_get_contents("https://api-bdc.net/data/hazard-report?ip=$ip&key=$bigdatakey")) === false) exit("tor failed");
  $istor = json_decode($json);
  $tordisp = null;
  foreach($istor as $k=>$v) {
    if($v) {
      $tordisp .= "<tr><td>$k</td><td>$v</td></tr>";
    }
  }

  if(!empty($tordisp)) {
    $ip2info .= $tordisp;
  }
              
  if(($json = file_get_contents("https://api-bdc.net/data/user-risk?ip=$ip&key=$bigdatakey")) === false) exit("tor failed");
  $istor = json_decode($json);
  $ip2info .= "<tr><td>RiskLevel</td><td>$istor->description</td></tr></table>";
  
  return [$trackerTbl, $botsTbl, $locstr, $geoTbl, $badTbl, $ip2info];
}

// Start Here

if($_POST['page'] == 'find') {
  // If a POST

  $where = $_POST['where'];
  $and = $_POST['and'];
  $by = $_POST['by'];
  $value = "$where $and $by";
  $sql = $_POST['sql'];
  $sql = $sql ?? $val;
} elseif($_GET) {
  // If a GET
  $sql = $_GET['sql'];
  $where = $_GET['where'];
  $and = $_GET['and'];
  $by = $_GET['by'];
  if(!str_contains($by, 'order')) {
    error_log("findip.php GET, 'by' error: site=$S->site, ip=$S->ip, page=$S->self, agent=$S->agent");
    exit('Go Away');
  }
  $value = "$where $and $by";
  $sql = $sql ?? $val;
} 
      
if(!empty($sql)) {
  [$trackerTbl, $botsTbl, $locstr, $geoTbl, $badTbl, $ip2info] = getinfo($value, $sql);
} else {
  $where = "where site='Bartonphillips'";
  $and = "and lasttime>current_date()";
  $by = "order by lasttime";
}

$S->banner = "<h1>Find in Tracker</h1>";
$S->title = "Find in Tracker";

$S->css =<<<EOF
/* 2 is ip address */
#trackertbl td:nth-of-type(2) {
  cursor: pointer;
}
/* 4 is page*/
#trackertbl td:nth-of-type(4) {
  overflow-x: auto; max-width: 150px; white-space: pre;
  cursor: pointer;
}
/* 9 is agent */
#trackertbl td:nth-of-type(9) {
  overflow-x: auto; max-width: 200px; white-space: pre;
  cursor: pointer;
}
/* 10 is referer */
#trackertbl td:nth-of-type(10) { overflow-x: auto; max-width: 150px; white-space: pre;}
/* 11 is javascript */
#trackertbl td:nth-of-type(11) { cursor: pointer;} 
#trackerContainer, #botsContainer, #mygeo, #badplayer, #daycounts {
  width: 100%;
}
#trackertbl { position: relative; font-size: 16px; width: 100%; }
#trackerContainer td { padding-left: 10px; padding-right: 10px; }
#botsContainer td { padding-left: 10px; padding-right: 10px; }
/* agent field */
#botsContainer td:nth-of-type(2) { word-break: break-all; width: 900px; }
#botstbl, #mygeo, #dayrecords, #badplayer { width: 100%; }
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

require_once("/var/www/bartonlp.com/otherpages/setupjava.i.php");

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
  // 2 is the ip

  $("body").on("dblclick", "#trackertbl td:nth-child(2)", function(e) {
    const myip = $(this).text();

/*    $.ajax("./findip.php", {
      data: {page: "find", where: "where ip='" +myip+"'", and: "and lasttime>current_date() -interval 5 day", by: "order by lasttime"},
      type: "get"
    });
*/
    window.location.replace("https://bartonphillips.com/findip.php?where=where%20ip%3D%27"+myip+"%27&and=and%20lasttime>current_date()%20-interval%205%20day&by=order%20by%20lasttime%20desc");
    e.stopPropagation();
  });

  // 4 & 9 is the page & agent
  // When clicked show the whole page or agent string.

  $("body").on("click", "#trackertbl td:nth-child(4), #trackertbl td:nth-child(9)", function(e) {
    if(e.ctrlKey && $(this)[0].cellIndex == 8) {
      const txt = $(this).text();
      const pat = /(http.?:\/\/.*?)\)/;
      const found = txt.match(pat);
      //console.log("found:", found);
      if(found) {
        window.open(found[1], "bot");
      }
      e.stopPropagation();
    } else {
      let ypos, xpos;
      let pos = $(this).position();
      xpos = pos.left - 200;
      ypos = pos.top;

      $("#Human").remove();
      $("#Agent").remove();
      $("#Page").remove();

      $("#trackertbl").append("<div id='Agent' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                              "background-color: white; border: 5px solid black; "+
                              "padding: 10px;'>"+$(this).text()+"</div>");
      e.stopPropagation();
    }
  });

  // 11 is the java script value.
  // Show the human readable values.

  $("body").on("click", "#trackertbl td:nth-child(11)", function(e) {
    let js = parseInt($(this).text(), 16),
    h = '', ypos, xpos;
    let pos = $(this).position(); // get the top and left

    xpos = pos.left - 300; // Push this to the left so it will render full size

    ypos = pos.top;

    for(let [k, v] of Object.entries(tracker)) {
      h += (js & k) ? v + "<br>" : '';
    }
    
    $("#Human").remove();
    $("#Agent").remove();
    $("#Page").remove();

    $("#trackertbl").append("<div id='Human' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                 "background-color: white; border: 5px solid black; "+
                 "padding: 10px;'>"+h+"</div>");

    xpos = pos.left - ($("#Human").width() + 35); // we add the border and padding (30px) plus a mig.
    $("#Human").css("left", xpos + "px");
        
    e.stopPropagation();
  });
  $("body").on("click", function(e) {
    $("#Human").remove();
    $("#Agent").remove();
    $("#Page").remove();
  });
EOF;

[$top, $footer] = $S->getPageTopBottom();

$sqlStatment = $sqlval ? "<p>Sql Statment:<br>$sqlval</p>" : null;

echo <<<EOF
$top
$sqlStatment
<p>Select your query fields</p>
<form method='post'>
<table id='form'>
<tbody>
<tr><th>Enter Fields</th><td><input type="text" name="sql" value="$val"></td></tr>
<tr><th>Where</th><td><input type="text" name="where" value="$where"></td></tr>
<tr><th>and</th><td><input type="text" name="and" value="$and"></td></tr>
<tr><th>Order By</th><td><input type="text" name="by" value="$by"></td></tr>
</tbody>
</table>
<button type='submit' name='page' value='find'>Find</button>
</form>
<div id='locationx'>$locstr</div>
<div id='ip2info'>$ip2info</div>
<div id='trackerContainer'><p>From tracker table</p>$trackerTbl</div>
<div id='botsContainer'>$botsTbl</div>
<div id='geo'>$geoTbl</div>
<div id='badContainer'>$badTbl</div>
<div id="outer">
  <div id="geocontainer"></div>
  <button id="removemsg">Click to remove map image</button>
</div>
$footer
EOF;

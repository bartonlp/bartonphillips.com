<?php
// BLP 2023-02-25 - use new approach
// Given the ip find the records in tracker, geo, bots.
/*
CREATE TABLE `tracker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botAs` varchar(30) DEFAULT NULL,
  `site` varchar(25) DEFAULT NULL,
  `page` varchar(255) NOT NULL DEFAULT '',
  `finger` varchar(50) DEFAULT NULL,
  `nogeo` tinyint(1) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `agent` text,
  `referer` varchar(255) DEVAULT '',
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `difftime` varchar(20) DEFAULT NULL,
  `isJavaScript` int DEFAULT '0',
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

CREATE TABLE `dayrecords` (
  `fid` int DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `site` varchar(20) DEFAULT NULL,
  `page` varchar(255) DEFAULT NULL,
  `finger` varchar(20) DEFAULT NULL,
  `jsin` varchar(10) DEFAULT NULL,
  `jsout` varchar(20) DEFAULT NULL,
  `dayreal` int DEFAULT NULL,
  `rcount` int DEFAULT '0',
  `daybots` int DEFAULT NULL,
  `dayvisits` int DEFAULT NULL,
  `visits` smallint DEFAULT '0',
  `lasttime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$T = new dbTables($S);

$val = "select id,ip,site,page,botAs,finger,nogeo,agent,referer,hex(isjavascript) as java,starttime,endtime,difftime,lasttime from $S->masterdb.tracker ";

// These MUST BE TRUE globals!!
$fingers = [];
$ip = null;

function getinfo($value, $sql=null) {
  global $S, $T, $fingers, $ip, $where, $by, $and, $sqlval;

  // Here $where is either the id or ip value or the full phrase 'where id/ip=...'
  // So if $value then we make it 'where $value' and if it is a POST that calls this we use the
  // $_POST['where'] which is the full phrase 'where id/ip=...'

  $where = $_POST['where'] ?? "where $value";
  $by = $_POST['by'] ?? " order by starttime";
  if(!str_contains($value, 'id=')) {
    $and = $_POST['and'] ?? " and starttime>=current_date() - interval 10 day";
  }
  $sql = "$sql {$where}{$and}{$by}";
  $sqlval = $sql;
  $val = $sql;

  //echo "$sql<br>";

  $ip = null;
  
  function trackerCallback(&$row, &$desc) {
    global $fingers, $ip; // these are true globals, outside of getinfo().

    if(is_null($ip)) {
      $ip = $row['ip']; // Do this once. Get ip from tracker table
    }
    
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

  // If we are using ip use it if we are doing id set the $bitsWhere to the ip we got in the
  // callback.
  
  if(preg_match("~ip=['\"](.*)['\"]~", $where, $m)) {
    $ip = $m[1];
    $botsWhere = $where; // This is the full phrase.
  } else {
    $botsWhere = "where ip='$ip'"; // $ip from callback
  }

  // At this point $ip is either the ip form the $where or the ip from the callback.
  
  $sql = "select ip, agent, count, hex(robots) as robots, site, creation_time, lasttime from $S->masterdb.bots $botsWhere order by lasttime";
  $botsTbl = $T->maketable($sql, ['attr'=>['id'=>'botstbl', 'border'=>'1']])[0];
  
  if($botsTbl) {
    $botsTbl = "<p>From bots table</p>$botsTbl";
  } else {
    $botsTbl = "<h1>Not in bots table</h1>";
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

  // Again, $ip is either the original ip form $where or the ip from the callback, which could be
  // null if there was no tracker record.
  
  if(!empty($ip)) {
    $sql = "select fid, ip, site, page, jsin, jsout, dayreal, rcount, daybots, dayvisits, visits, lasttime ".
           "from $S->masterdb.dayrecords where ip='$ip' order by lasttime";
    $dayrecords = $T->maketable($sql, ['attr'=>['id'=>'dayrecords', 'border'=>'1']])[0];
  }
  
  if($dayrecords) {
    $dayrecords = "<p>From dayrecords table</p>$dayrecords";
  } else {
    $dayrecords = "<h1>Not in dayrecords table</h1>";
  }

  if($ip) {
    $loc = json_decode(file_get_contents("http://ipinfo.io/$ip")); // I could also use the class link in webstats.js
    $gpsloc = $loc->loc;
    $locstr = <<<EOF
<p>User Information from "http://ipinfo.io/$ip"</p>

<ul class="user-info">
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li>GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
</ul>
EOF;
  } else {
    $locstr = "<h1>No Location Data Available from <i>http://ipinfo.io</i></h1>";
  }

  if(!empty($ip)) {
    $sql = "select ip, botAs, type, count, errno, errmsg, agent, lasttime from $S->masterdb.badplayer where ip='$ip'";
    $badTbl = $T->maketable($sql, ['attr'=>['id'=>'badplayer', 'border'=>'1']])[0];
  }
  if($badTbl) {
    $badTbl = "<p>From badplayer table</p>$badTbl";
  } else {
    $badTbl = "<h1>Not in badplayer table</h1>";
  }
  
  return [$trackerTbl, $botsTbl, $locstr, $geoTbl, $dayrecords, $badTbl];
}

// Start Here

if($_POST['page'] == 'find') {
  // Here value will be the full phrase "where id/ip=..."
  $value = $_POST['where'];
  $sql = $_POST['sql'];
  [$trackerTbl, $botsTbl, $locstr, $geoTbl, $dayrecords, $badTbl] = getinfo($value, $sql);
} elseif($_GET['ip']) {
  $ip = "ip='{$_GET['ip']}'";
  [$trackerTbl, $botsTbl, $locstr, $geoTbl, $dayrecords, $badTbl] = getinfo($ip, $val);
} elseif($_GET['id']) {
  $id = "id={$_GET['id']}";
  [$trackerTbl, $botsTbl, $locstr, $geoTbl, $dayrecords, $badTbl] = getinfo($id, $val);
}

$S->banner = "<h1>Find in Tracker</h1>";
$S->title = "Find in Tracker";

$S->css =<<<EOF
/* 10 is javascript */
#trackertbl td:nth-of-type(10) { cursor: pointer; } 
#trackerContainer, #botsContainer, #geo {
  width: 100%;
}
#trackertbl { position: relative; font-size: 10px; }
#trackerContainer td { padding-left: 10px; padding-right: 10px; }
#botsContainer td { padding-left: 10px; padding-right: 10px; }
/* agent filed */
#botsContainer td:nth-of-type(2) { word-break: break-all; width: 900px; }
#geo td { padding-left: 10px; padding-right: 10px; }
#badplayer td { padding-left: 10px; padding-right: 10px; }
#form th { text-align: left; padding-right: 10px; font-size: 18px; }
#location { font-size: 18px; }
input { width: 1000px; font-size: 18px; padding-right: 10px; padding-left: 10px; }
button { border-radius: 5px; background: green; color: white; font-size: 18px; }
/* mygeo is the table */
#mygeo td {
  cursor: pointer;
}
#mygeo th {
  width: 220px;
}
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
#location li:nth-of-type(2) i { cursor: pointer; }
.green { color: green; }
.botas { color: green; }
EOF;

function setupjava($S) {
  $start = TRACKER_START;
  $load = TRACKER_LOAD;
  $script = TRACKER_SCRIPT;
  $normal = TRACKER_NORMAL;
  $noscript = TRACKER_NOSCRIPT;
  $bvisibilitychange = BEACON_VISIBILITYCHANGE;
  $bpagehide = BEACON_PAGEHIDE;
  $bunload = BEACON_UNLOAD;
  $bbeforeunload = BEACON_BEFOREUNLOAD;
  $tbeforeunload = TRACKER_BEFOREUNLOAD;
  $tunload = TRACKER_UNLOAD;
  $tpagehide = TRACKER_PAGEHIDE;
  $tvisibilitychange = TRACKER_VISIBILITYCHANGE;
  $timer = TRACKER_TIMER;
  $bot = TRACKER_BOT;
  $css = TRACKER_CSS;
  $me = TRACKER_ME;
  $goto = TRACKER_GOTO; // Proxy
  $goaway = TRACKER_GOAWAY; // unusal tracker.

  $S->h_inlineScript = <<<EOF
    const tracker = {
  "$start": "Start", "$load": "Load", "$script": "Script", "$normal": "Normal",
  "$noscript": "NoScript", "$bvisibilitychange": "B-VisChange", "$bpagehide": "B-PageHide", "$bunload": "B-Unload", "$bbeforeunload": "B-BeforeUnload",
  "$tbeforeunload": "T-BeforeUnload", "$tunload": "T-Unload", "$tpagehide": "T-PageHide", "$tvisibilitychange": "T-VisChange",
  "$timer": "Timer", "$bot": "BOT", "$css": "Csstest", "$me": "isMe", "$goto": "Proxy", "$goaway": "GoAway"
  };
  EOF;
}

setupjava($S);

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
  // 10 is the java script value.

  $("body").on("click", "#trackertbl td:nth-child(10)", function(e) {
    let js = parseInt($(this).text(), 16),
    h = '', ypos, xpos;
    let pos = $(this).position(); // get the top and left

    xpos = pos.left - 300; // Push this to the left so it will render full size

    ypos = pos.top;

    for(let [k, v] of Object.entries(tracker)) {
      h += (js & k) ? v + "<br>" : '';
    }
    
    $("#Human").remove();

    $("#trackertbl").append("<div id='Human' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                 "background-color: white; border: 5px solid black; "+
                 "padding: 10px;'>"+h+"</div>");

    xpos = pos.left - ($("#Human").width() + 35); // we add the border and padding (30px) plus a mig.
    $("#Human").css("left", xpos + "px");
        
    e.stopPropagation();
  });
  $("body").on("click", function(e) {
    $("#Human").remove();
  });
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<p>Sql Statment:<br>$sqlval</p>
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
<div id='location'>$locstr</div>
<div id='trackerContainer'>$trackerTbl</div>
<div id='botsContainer'>$botsTbl</div>
<div id='geo'>$geoTbl</div>
<div id='dayrecord'>$dayrecords</div>
<div id='badContainer'>$badTbl</div>
<div id="outer">
<div id="geocontainer"></div>
<button id="removemsg">Click to remove map image</button>
</div>
$footer
EOF;

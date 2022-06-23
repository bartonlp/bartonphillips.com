<?php
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
*/

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);
$T = new dbTables($S);

$val = 'id,site,page,botAs,finger,agent,hex(isjavascript) as java,starttime,endtime,difftime,lasttime';

$fingers = [];

function getinfo($ip, $sql=null, $val=null) {
  global $S, $T, $fingers;

  $val = $val ?? "id,site,page,botAs,finger,agent,hex(isjavascript) as java,starttime,endtime,difftime,lasttime";
  $sql = $sql ?? "select $val from $S->masterdb.tracker where ip='$ip' and lasttime>=current_date() - interval 10 day order by lasttime";

  function trackerCallback(&$row, &$desc) {
    global $fingers;
    $fingers[$row['finger']]++;
    $t = $row['difftime'];
    $hr = $t/3600;
    $min = ($t%3600)/60;
    $sec = ($t%3600)%60;
    $row['difftime'] = sprintf("%u:%02u:%02u", $hr, $min, $sec);
  }

  $trackerTbl = $T->maketable($sql, ['callback'=>'trackerCallback', 'attr'=>['id'=>'trackertbl', 'border'=>'1']])[0];
  $keys = implode(",", array_keys($fingers));

  $sql = "select ip, agent, count, hex(robots) as robots, site, creation_time, lasttime from $S->masterdb.bots where ip='$ip' order by lasttime";

  $botsTbl = $T->maketable($sql, ['attr'=>['id'=>'botstbl', 'border'=>'1']])[0];
  $botsTbl =  $botsTbl ?? "<h1>Not in bots table</h1>";

  $sql = "select lat, lon, finger, site, created, lasttime from $S->masterdb.geo where finger in('$keys') order by lasttime";
  
  $geoTbl = $T->maketable($sql, ['attr'=>['id'=>'mygeo', 'border'=>'1']])[0];
  $geoTbl = $geoTbl ?? "<h1>Not in geo table</h1>";
  
  $loc = json_decode(file_get_contents("http://ipinfo.io/$ip"));

  $locstr = <<<EOF
<p>User Information from 'http://ipinfo.io/$ip'</p>
<ul class="user-info">
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li>GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
</ul>
EOF;
  return [$trackerTbl, $botsTbl, $locstr, $geoTbl];
}

if($_POST['page'] == 'find') {
  $ip = $_POST['ip'];
  $sql = $_POST['sql'];
  $val = $sql;

//  $sql = preg_replace("~isjavascript~", "hex(isjavascript) as java", $sql);
  $sql = "select $sql from $S->masterdb.tracker where ip='$ip' and lasttime>=current_date() - interval 10 day order by lasttime";

  [$trackerTbl, $botsTbl, $locstr, $geoTbl] = getinfo($ip, $sql, $val);
} elseif($_GET['ip']) {
  $ip = $_GET['ip'];
  [$trackerTbl, $botsTbl, $locstr, $geoTbl] = getinfo($ip);
}

$h->banner = "<h1>Find in Tracker</h1>";
$h->css =<<<EOF
#trackerContainer, #botsContainer, #geoContainer {
  width: 100%;
  font-size: 16px;
}
#trackerContainer td { padding-left: 10px; padding-right: 10px; }
#botsContainer td { padding-left: 10px; padding-right: 10px; }
#geoContainer td { padding-left: 10px; padding-right: 10px; }
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
  margin-right: auto; */
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
EOF;

// To get a geo do either $h or $b->inlineScript = "var doGeo = true;";
//$h->inlineScript = "var doGeo = true;";

$b->script =<<<EOF
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

$b->noCounter = true; // No counter.

[$top, $footer] = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<p>Select your query fields</p>
<form method='post'>
<table id='form'>
<tbody>
<tr><th>Enter Fields</th><td><input type='text' name='sql' value='$val'></td></tr>
<tr><th>Enter IP</th><td><input type='text' name='ip' value='$ip'></td></tr>
</tbody>
</table>
<button type='submit' name='page' value='find'>Find</button>
</form>
<div id='location'>$locstr</div>
<div id='trackerContainer'>$trackerTbl</div>
<p>From bots table</p>
<div id='botsContainer'>$botsTbl</div>
<p>From geo table</p>
<div id='geoContainer'>$geoTbl</div>
<div id="outer">
<div id="geocontainer"></div>
<button id="removemsg">Click to remove map image</button>
</div>
$footer
EOF;


<?php
// BLP 2024-12-15 - 
// Given the ip find the records in tracker, geo, bots.
// Given a 'where' clause.
// Given an 'and' clause.
/*
CREATE TABLE `tracker` (
  `id` bigint NOT NULL AUTO_INCREMENT,
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
*/

if(!class_exists('Database')) header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=bartonphillips.com&page=/head.i.php");

$T = new dbTables($S);

$val = "select id,ip,site,page,botAs,finger,nogeo,browser,agent,referer,hex(isjavascript) as java,error,starttime,endtime,difftime,lasttime ".
       "from $S->masterdb.tracker ";

// These MUST BE TRUE globals!!
$fingers = []; // from the tracker table
$ip = null; // also from tracker
// ****************************

function getinfo($value=null, $sql) {
  // If the 'where' clause had ip='...' then $ip has that value.
  // Otherwise $ip is null.
  
  global $S, $T, $fingers, $sqlval, $ip;

  $sql = "{$sql} {$value}";
  $sqlval = $sql; // global used in first line of render.

  // Callback for tracker
  
  function trackerCallback(&$row, &$desc) {
    global $fingers, $ip; // these are true globals, outside of getinfo().

    if(is_null($ip)) { // If the 'where' clause had ip='...' then ip is already set!
      $ip = $row['ip']; // Do this once. Get ip from tracker table
    }
      
    $row['referer'] = basename($row['referer']);
    
    if($row['finger'] != '') {
      $fingers[] = $row['finger'];
    }
    
    $t = $row['difftime'];
    $hr = $t/3600;
    $min = ($t%3600)/60;
    $sec = ($t%3600)%60;
    $row['difftime'] = sprintf("%u:%02u:%02u", $hr, $min, $sec);
  }

  // Desc callback for tracker

  function trackerCallback2(&$desc) {
    // Add a id and ip class to the first and second td.
    
    $desc = preg_replace("~<tr><td>(.*?)</td><td>(.*?)</td>~", "<tr><td class='id'>$1</td><td class='ip'>$2</td>", $desc);
  }

  // Create tracker table
  
  $trackerTbl = $T->maketable($sql, ['callback'=>'trackerCallback', 'callback2' => 'trackerCallback2', 'attr'=>['id'=>'trackertbl', 'border'=>'1']])[0];

  // If $trackerTbl is not empty add the table to the message.
  
  $trackerTbl = empty($trackerTbl) ? "<h1>Not in tracker table</h1>" : "<h1>From tracker table</h1>$trackerTbl";
  
  // $fingers is from the trackerCallback().
  // NOTE: The $fingers array only has the 'and' clause info form the tracker table. So if the
  // 'and' clause has a date restriction like 'lasttime>now() - interval 5 day' you will not see
  // all of the goe information.
  
  foreach($fingers as $val) {
    $vals .= "'$val',";
  }
  $vals = rtrim($vals, ','); // BLP 2025-01-30 - remove trailing comma.
  
  //  Create the bots table

  $sql = "select ip, agent, count, hex(robots) as robots, site, creation_time, lasttime from $S->masterdb.bots where ip='$ip' order by lasttime";
  
  $botsTbl = $T->maketable($sql, ['attr'=>['id'=>'botstbl', 'border'=>'1']])[0];

  $botsTbl = empty($botsTbl) ? "<h1>Not in bots table</h1>" : "<h1>From bots table</h1>$botsTbl";

  // Create the geo table. $vals is a string created from the $fingers array. This is from trackerCallback().
  
  $sql = "select lat, lon, finger, site, created, lasttime from $S->masterdb.geo where finger in($vals) order by lasttime";

  if($vals) {
    $geoTbl = $T->maketable($sql, ['attr'=>['id'=>'mygeo', 'border'=>'1']])[0];
  }

  $geoTbl = empty($geoTbl) ? "<h1>Not in geo table</h1>" : "<p>From geo table</p>$geoTbl";

  // Create the $locstr from ipinfo.io.
  
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

  // Create the badplayer table if ip is not empty.

  if(!empty($ip)) {
    $sql = "select ip, botAs, type, errno, errmsg, agent, created, lasttime from $S->masterdb.badplayer where ip='$ip' order by lasttime";
    $badTbl = $T->maketable($sql, ['attr'=>['id'=>'badplayer', 'border'=>'1']])[0];
  }

  $badTbl = empty($badTbl) ? "<h1>Not in badplayer table</h1>" : "<h1>From badplayer table</h1>$badTbl";

  // Create additional info from api.ip2location.io
  
  $key = require '/var/www/PASSWORDS/Ip2Location-key';
  $bigdatakey = require '/var/www/PASSWORDS/BigDataCloudAPI-key';
  
  if(($json = file_get_contents("https://api.ip2location.io/?key=$key&ip=$ip")) === false) {
    error_log("findip: ip2location failed");
    exit("<h1>Not a Valid IP</h1><p>ip2location failed</p>");
  }
  
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
  
  if(($json = file_get_contents("https://api-bdc.net/data/hazard-report?ip=$ip&key=$bigdatakey")) === false) {
    error_log("findip: Call to 'https://api-bdc.net/data/hazard-report?ip=$ip&key=$bigdatakey' failed");
    exit("<h1>tor failed 1</h1>");
  }
  
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
  
  if(($json = file_get_contents("https://api-bdc.net/data/user-risk?ip=$ip&key=$bigdatakey")) === false) {
    error_log("findip: get RiskLevel from api-bdc.net failed");
    exit("<h1>tor failed 2</h1>");
  }
  $istor = json_decode($json);
  
  $ip2info .= empty($istor->description) ? "<tr><td colspan='2'>No RiskLevel found</td></tr></table>" :
              "<tr><td>RiskLevel</td><td>$istor->description</td></tr></table>";
  
  return [$trackerTbl, $botsTbl, $locstr, $geoTbl, $badTbl, $ip2info];
}

// ********************************
// Start Here
// ********************************

$where = $_POST['where'];
preg_match("~ip='(\d+\.\d+\.\d+\.\d+)'~", $where, $m);
$searchIp = $m[1];

$and = $_POST['and'];
$by = $_POST['by'];
$value = "$where $and $by";
$sql = $_POST['sql'] ?? $val;

$S->banner = "<h1>Find in Tracker</h1>";
$S->title = "Find in Tracker";

$ref = $_SERVER['HTTP_REFERER'];

$S->css =<<<EOF
/* 2 is ip address */
#trackertbl td:nth-of-type(1), #trackertbl td:nth-of-type(2) {
  cursor: pointer;
}
/* 4 is page*/
#trackertbl td:nth-of-type(4) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}
/* 5 is botAs */
#trackertbl td:nth-of-type(5) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}
/* 6 is finger */
#trackertbl td:nth-of-type(6) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}
/* 9 is agent */
#trackertbl td:nth-of-type(9) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}
/* 10 is referer */
#trackertbl td:nth-of-type(10) {
  overflow-x: auto; max-width: 100px; white-space: pre;
  cursor: pointer;
}
/* 11 is javascript */
#trackertbl td:nth-of-type(11) { cursor: pointer;}
/* 12 is error */
#trackertbl td:nth-of-type(12) {
  overflow-x: auto;
  max-width: 100px;
  white-space: pre;
  cursor: pointer;
}

#trackerContainer, #botsContainer, #mygeo, #badplayer, #daycounts {
  width: 100%;
}
#trackertbl { position: relative; font-size: 16px; width: 100%; }
#trackerContainer td { padding-left: 10px; padding-right: 10px; }
#botsContainer td { padding-left: 10px; padding-right: 10px; }
/* agent field */
#botsContainer td:nth-of-type(2) { word-break: break-all; width: 900px; }
#botstbl, #mygeo, #dayrecords, #badplayer { width: 100%; }
#botstbl { position: relative; }
#botstbl td:nth-of-type(4) { cursor: pointer; }
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
  
  // Disable all columns except 12 and 15 (starttime, lasttime). Note tablesorter is zero based.

  for(let i = 0; i < 16; i++) {
    if(i !== 12 && i !== 15) {
      headers[i] = { sorter: false };
    }
  }

  $("#trackertbl").tablesorter({theme: 'blue', headers: headers});

  // trackertbl td 1 is the ID, td 2 is IP.

  $("body").on("click", ".id, .ip", function(e) {
    const idOrIp = $(this).text();
    const cl = e.currentTarget.className;
  
    const where = "where " +cl+"='" +idOrIp+ "'";
    const and = "and lasttime>current_date() -interval 5 day";
    const by = "order by lasttime desc";
    const data = JSON.stringify({ message: [where, and, by] });
   
    location.replace("findip2.php?data=" + data);
  });

  // trackertbl td 4, 5,6, 9, 10 and 12 are the page, botAs, finger, referer, error.
  // When clicked show the whole page or agent string.

  $("body").on("click", "#trackertbl td:nth-of-type(4), #trackertbl td:nth-of-type(5), "+
                        "#trackertbl td:nth-of-type(6), #trackertbl td:nth-of-type(9), "+
                        "#trackertbl td:nth-of-type(10), #trackertbl td:nth-of-type(12)",
                        function(e) {
    // A ctrl key and cellIndex 8 which is td 9.

    if(e.ctrlKey && $(this)[0].cellIndex == 8) {
      const txt = $(this).text();
      const pat = /(https?:\/\/.*?)(?:\)|;)/; // The text must start with http etc.
      const found = txt.match(pat);

      if(found) {
        // open the window in a new tab.

        window.open(found[1], "bot");
      }
      e.stopPropagation();
    } else {
      // Note ctrl key and cellIndex 8
      // Just a normal click.

      let ypos, xpos;
      let pos = $(this).position();
      xpos = pos.left - 200;
      ypos = pos.top;

      removeAll();

      $("#trackertbl").append("<div id='FindBot' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                              "background-color: white; z-index: 1000; border: 5px solid black; "+
                              "padding: 10px;'>"+$(this).text()+"</div>");
      e.stopPropagation();
    }
  });

  // trackertbl td 11 is the java script value.
  // botstbl td 4 is the robots value
  // Show the human readable values.

  $("body").on("click", "#trackertbl td:nth-of-type(11), #botstbl td:nth-of-type(4)", function(e) {
    let js = parseInt($(this).text(), 16),
    h = '', ypos, xpos;
    let human;

    // Make it look like a hex. Then and it with 0x100 if it is true
    // then make js 0x1..
    
    //if('0x'+js & 0x100) js='0x'+js;
    
    let table = $(this).closest("table");
    let pos = $(this).position(); // get the top and left
    
    // The td is in a tr which in in a tbody, so table is three
    // prents up.

    if(table.attr("id") != 'trackertbl') {
      // Robots (bots table)
      
      human = robots; // robots was set in webstats.php in the inlineScript.
      
      xpos = pos.left + $(this).width() + 17; // add the one border and one padding (15px) plus a mig.
    } else {
      // Tracker table.
      
      human = tracker; // tracker was set in webstats.php in the inlineScript
      
      xpos = pos.left - 300; // Push this to the left so it will render full size
    }
    ypos = pos.top;

    //console.log("human:", human);

    for(let [k, v] of Object.entries(human)) {
      h += (js & k) ? v + "<br>" : '';
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

  // BroadcastChannel logic.
  // showErrorLog2.php uses BroadcastChannel to pass data to these functions

  const channel = new BroadcastChannel('myOtherTab');

  channel.onmessage = (event) => {
    if(event.data.type === 'is_open?') {
      channel.postMessage({ type: 'is_open' }); // Confirm this tab is open
    } else if(event.data.type === 'update') {
      console.log('Received data:', event.data.payload);
      handleIncomingData(event.data.payload);
    } else if(event.data.type === 'focus_request') {
      flashTitle();
    }
  };

function flashTitle() {
    let originalTitle = document.title;
    let flash = true;

    const interval = setInterval(() => {
        document.title = flash ? "🔴 New Log Data!" : originalTitle;
        flash = !flash;
    }, 1000);

    // Stop flashing when the user focuses the tab
    window.addEventListener("focus", () => {
        clearInterval(interval);
        document.title = originalTitle;
    });
}

  function handleIncomingData(data) {
    console.log('Received data:', data);
    const x = JSON.parse(data);
    console.log("x: ", x);
    const where = x.message[0];
    const and = x.message[1];
    const by = x.message[2];

    $.ajax({
      url: 'findip2.php',
      data: { page: 'find', where: where, and: and, by: by, type: 'post' },
      type: 'post',
      success: function(data) {
        const y = JSON.parse(data);

        console.log("y: ", y);
        
        $("#form input[name='where']").val(where);
      
        $("#trackerContainer").html(y[0]);
        $("#locationx").html(y[2]);
        $("#ip2info").html(y[5]);
        $("#botsContainer").html(y[1]);
        $("#geo").html(y[3]);
        $("#badContainer").html(y[4]);
      },
      error: function(e) {
        console.log("ERROR: ", e);
      }
    });
    //location.replace("findip2.php?data=" + data);
  }
EOF;

[$top, $footer] = $S->getPageTopBottom();

$sqlStatment = $sqlval ? "<p>Sql Statment:<br>$sqlval</p>" : null;

// Note, the <form does not set 'type' so the POST falls through to this page.

echo <<<EOF
$top
<hr>
$sqlStatment
<p>Select your query fields</p>
<form method='post' action="./findip2.php">
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
<div id='botsContainer'>$botsTbl</div>
<div id='geo'>$geoTbl</div>
<div id='badContainer'>$badTbl</div>
<div id="outer">
  <div id="geocontainer"></div>
  <button id="removemsg">Click to remove map image</button>
</div>
<hr>
$footer
EOF;

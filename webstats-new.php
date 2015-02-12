<?php
// BLP 2014-11-02 -- make tracker average stay reflect the current state of the table.
// BLP 2014-08-30 -- change $av to only look at last 3 days and to allow only times less the 2hr.

require_once("/var/www/includes/siteautoload.class.php");

$referer = $_SERVER['HTTP_REFERER'];

if(!preg_match("/bartonphillips\.com|org|net/", $referer)) {
  echo <<<EOL
<h1>Access Forbiden</h1>
<p>Please go away.</p>

EOL;
  exit();
}

$S = new Blp;

$T = new dbTables($S);

$h->link = <<<EOF
  <!-- local css links -->
  <link rel="stylesheet"  href="css/tablesorter.css" type="text/css">
EOF;

$h->extra = <<<EOF
  <!-- local script files -->
  <script src="js/tablesorter/jquery.tablesorter.js"></script>
  <script src="js/tablesorter/jquery.metadata.js"></script>
EOF;

$h->script = <<<EOF
  <!-- local inline script -->
  <script>
jQuery(document).ready(function($) {
  var flags = {webmaster: false, robots: false, ip: false , page: false};

  $("#blpmembers, #logagent, #counter, #tracker").tablesorter()
    .addClass('tablesorter'); // attach class tablesorter to all except our counter

  // Don't show webmaster

  var myIp = "$S->myIp";

  // Check Flags look at other flags

  function checkFlags(flag) {
    var msg;

    if(flag) { // Flag is true.
      switch(flag) {
        case 'webmaster': // default is don't show
          $(".webmaster").parent().hide();
          msg = "Show ";
          flags.webmaster = false;
          break;
        case 'robots': // true means we are showing robots
          $('.robots').parent().hide();
          msg = "Show ";
          flags.robots = false;
          break;
        case 'ip': // true means only this ip is showing so we want to make all ips show
          $(".ip").removeClass('ip');
          $("#tracker tr").show();

          if(flags.page) {
            $("#tracker td:first-child:not('.page')").parent().hide();
          }
             
          if(!flags.webmaster) {
            $(".webmaster").parent().hide();
          }
          if(!flags.robots) {
            $(".robots").parent().hide();
          }
          msg = "Only ";
          flags.ip = false;
             
          break;
        case 'page': // true means we are only showing this page
          $(".page").removeClass('page');
          $("#tracker tr").show();
                          
          if(flags.ip) {
            $("#tracker td:nth-child(2):not('.ip')").parent().hide();
          }

          if(!flags.webmaster) {
            $(".webmaster").parent().hide();
          }
          if(!flags.robots) {
            $(".robots").parent().hide();
          }
          msg = "Only ";
          flags.page = false;
          break;
      }
      $("#"+ flag).text(msg + flag);
      calcAv();
      return;
    }   

    for(var f in flags) {
      if(flags[f]) { // if true
        switch(f) {
          case 'webmaster':
            flags.webmaster = false;
            if(true in flags) {
              $(".webmaster").parent().not(":hidden").show();
            } else {
              $(".webmaster").parent().show();
            }
            flags.webmaster = true;
            msg = "Hide ";
            break;
          case 'robots':
            flags.robots = false;
            if(true in flags) {
              $('.robots').parent().not(":hidden").show();
            } else {
              $(".robots").parent().show();
            }
            flags.robots = true;
            msg = "Hide ";
            break;
          case 'ip': 
            $("#tracker tr td:nth-child(2):not('.ip')").parent().hide();
            msg = "All ";
            break;
          case 'page':
            $("#tracker tr td:first-child:not('.page')").parent().hide();
            msg = "All ";
            break;
        }
        $("#"+ f).text(msg + f);
      }   
    }
    calcAv();
  }

  function calcAv() {
    // Calculate the average time spend using the NOT hidden elements
    var av = 0, cnt = 0;
    $("#tracker tbody :not(:hidden) td:nth-child(6)").each(function(i, v) {
      var t = $(this).text();
      if(!t) return true; // Continue

      var ar = t.match(/^(\d{2}):(\d{2}):(\d{2})$/);
      t = parseInt(ar[1], 10) * 3600 + parseInt(ar[2],10) * 60 + parseInt(ar[3],10);
      if(t > 7200) return true; // Continue if over two hours 
 
      console.log("t: %d", t);
      av += t;
      ++cnt;      
    });

    av = av/cnt; // Average
   
    var hours = Math.floor(av / (3600)); 
   
    var divisor_for_minutes = av % (3600);
    var minutes = Math.floor(divisor_for_minutes / 60);
 
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);

    var tm = hours.pad()+":"+minutes.pad()+":"+seconds.pad();

    console.log("av time: ", tm);
    $("#average").html(tm);
  }

  Number.prototype.pad = function(size) {
    var s = String(this);
    while (s.length < (size || 2)) {s = "0" + s;}
    return s;
  }
 
  // To start Webmaster is hidden

  $("#tracker td:nth-child(2) span.co-ip").each(function(i, v) {
    if($(v).text() == myIp) {
      $(v).parent().addClass("webmaster").css("color", "green").parent().hide();
    }
  });

  calcAv();

  // To start Robots are hidden

  $(".bot td:nth-child(3)").addClass('robots').css("color", "red").parent().hide();
  
  // Put a couple of buttons before the table

  $("#tracker").before("<div id='trackerselectdiv'>"+
                       "<button id='webmaster'>Show webmaster</button>"+
                       "<button id='robots'>Show robots</button>"+
                       "<button id='page'>Only page</button>"+
                       "<button id='ip'>Only ip</button>"+
                       "</div>");

  // ShwoHide Webmaster clicked

  $("#webmaster").click(function(e) {
    if(flags.webmaster) {
      checkFlags('webmaster');
    } else {
      // Show
      flags.webmaster = true;
      // Now show only my IP
      checkFlags();
    }
    //flags.webmaster = !flags.webmaster;
  });

  // Page clicked

  $("#tracker td:first-child").click(function(e) {
    if(flags.page) {
      checkFlags('page');
    } else {
      // show only this page
      flags.page = true;
      var page = $(this).text();
      $("#tracker tr td:first-child").each(function(i, v) {
        if($(v).text() == page) {
          $(v).addClass('page');
        }
      });
      checkFlags();
    }
  });

  // IP address clicked

  $("#tracker td:nth-child(2)").click(function(e) {
    if(flags.ip) {
      checkFlags('ip');
    } else {
      // show only IP
      flags.ip = true;
      var ip = $(this).text();
      $("#tracker tr td:nth-child(2)").each(function(i, v) {
        if($(v).text() == ip) {
          $(v).addClass('ip');
        }
      });
      checkFlags();
    }
  });

  // ShowHideBots clicked

  $("#robots").click(function() {
    if(flags.robots) {
      // hide
      checkFlags('robots');
    } else {
      // show
      flags.robots = true;
      checkFlags();
    }
  });

  $("#ip").click(function() {
    if(flags.ip) {
      // hide
      checkFlags('ip');
    } else {
      // show
      alert("click on the IP address you want to show");
    }
  });

  $("#page").click(function() {
    if(flags.page) {
      // hide
      checkFlags('page');
    } else {
      // show
      alert("click on the page you want to show");
    }
  });
});
  </script>
EOF;

$h->css = <<<EOF
  <!-- local inline css -->
  <style>
button {
  -webkit-border-radius: 7px;
  -moz-border-radius: 7px;
  border-radius: 7px;
  font-size: 1.2em;
  margin-bottom: 10px;
}
.country {
  border: 1px solid black;
  padding: 3px;
  background-color: #8dbdd8;
}
.blpip {
  color: red;
}
th, td {
  padding: 5px;
}
#tracker {
  width: 100%;
  font-size: 16px;
}
#tracker td:nth-child(4), #tracker td:nth-child(5) {
  width: 5em;
}
#tracker td:last-child {
  word-break: break-all;
  word-break: break-word; /* for chrome */
}
#tracker td:nth-child(2):hover {
  cursor: pointer;
}
#tracker td:first-child:hover {
  cursor: pointer;
}
div {
  padding: 10px 0;
}
</style>
EOF;

$h->title = "Web Statistics";
$h->banner = "<h1>Web Stats For <b>bartonphillips.com</b></h1>";

list($top, $footer) = $S->getPageTopBottom($h,
  "<p>Return to <a href='index.php'>Home Page</a></p>\n<hr/>");

// webstats.i.txt is created by scripts/make-webstats.php

$page = file_get_contents("webstats.i.txt");

// Make tracker right now.

$sql = "select ip from tracker where starttime > date_sub(now(), interval 3 day)";

$S->query($sql);
$tkipar = array(); // tracker ip array

while(list($tkip) = $S->fetchrow('num')) {
  $tkipar[] = $tkip;
}

$list = json_encode($tkipar);

// Get this info from www.bartonlp.com/webstats-new.php

$ipcountry = file_get_contents("http://www.bartonlp.com/webstats-new.php?list=$list");
$ipcountry = (array)json_decode($ipcountry);

// maketable() callback

function callback(&$row, &$desc) {
  global $S, $ipcountry;

  $ip = $S->escape($row['ip']);

  $co = $ipcountry[$ip];

  $row['ip'] = "<span class='co-ip'>$ip</span><br><div class='country'>$co</div>";

  if($S->query("select ip from granbyrotarydotorg.bots where ip='$ip'")) {
    $desc = preg_replace("~<tr>~", "<tr class='bot'>", $desc);
  }
  
  $ref = urldecode($row['referrer']);
  // if google then remove the rest because google doesn't have an info in q= any more.
  if(strpos($ref, 'google') !== false) {
    $ref = preg_replace("~\?.*$~", '', $ref);
  }
  $row['referrer'] = $ref;
}

$sql = "select page, ip, agent, starttime, endtime, difftime, referrer ".
       "from tracker where starttime > date_sub(now(), interval 3 day) order by starttime desc";

list($tracker) = $T->maketable($sql, array('callback'=>callback,
                                           'attr'=>array('id'=>'tracker',
                                           'border'=>'1')));

// figure out the timezone of the server by doing 'date' which returns
// something like: Sun Dec 28 12:14:44 MST 2014
// Get the first letter of the time zone, like M for MST etc.

$ddate = preg_replace("/^.*?:\d\d (.).*/", '$1', exec("date"));

$zones = array("E"=>"America/New_York",
               "C"=>"America/Chicago",
               "M"=>"America/Denver",
               "P"=>"America/Los_Angeles"
              );

// Now set the timezone to the appropriate zone

date_default_timezone_set($zones[$ddate]);
$date = date("Y-m-d H:i:s T");

// Render the page

echo <<<EOF
$top
$date
<p>This report is gethered once each hour. Results are limited to 20 records.</p>
<ul>
   <li><a href="#table2">Goto Table Two: ip, agent</a></li>
   <li><a href="#table3">Goto Table Three: counter</a></li>
   <li><a href="#table4">Goto Table Four: counter2</a></li>
   <li><a href="#table5">Goto Table Five: daycounts</a></li>
   <li><a href="#table6">Goto Table Six: tracker</a></li>
</ul>   
$page
<h2 id="table6">Tracker (real time)</h2>
<p>Click on IP to show only that IP.<br>
Click on Page to show only that page.<br>
Click on Agent to see Country of IP.<br>
Average stay time: <span id="average"></span> (times over two hours are discarded.)</p>
<p>Showing only last 3 days.</p>
$tracker
$footer
EOF;

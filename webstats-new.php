<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

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

$extra = <<<EOF
<link rel="stylesheet"  href="css/tablesorter.css" type="text/css">
<script src="js/tablesorter/jquery.tablesorter.js"></script>
<script src="js/tablesorter/jquery.metadata.js"></script>

<script>
jQuery(document).ready(function($) {
  var flags = {webmaster: false, robots: false, ip: false , page: false};

  $("#blpmembers, #logagent, #memberpagecnt, #counter, #tracker").tablesorter()
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
  }
 
  // To start Webmaster is hidden

  $("#tracker td:nth-child(2)").each(function(i, v) {
    if($(v).text() == myIp) {
      $(v).addClass("webmaster").css("color", "green").parent().hide();
    }
  });

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

  <style>
button {
  -webkit-border-radius: 7px;
  -moz-border-radius: 7px;
  border-radius: 7px;
  font-size: 1.2em;
  margin-bottom: 10px;
}
th, td {
  padding: 5px;
}
#tracker {
  width: 100%;
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

$h = array('title'=>"Web Statistics", 'extra'=>$extra,
           'banner'=>"<h1>Web Stats For <b>bartonphillips.com</b></h1>");

$b = array('msg1'=>"<p>Return to <a href='index.php'>Home Page</a></p>\n<hr/>");

list($top, $footer) = $S->getPageTopBottom($h, $b);

$page = file_get_contents("webstats.i.txt");

function callback(&$row, &$desc) {
  global $S;

  $agent = $S->escape($row['agent']);

  if($S->query("select agent from barton11_granbyrotarydotorg.bots2 where agent='$agent'")) {
    $desc = preg_replace("~<tr>~", "<tr class='bot'>", $desc);
  }
  
  $ref = urldecode($row['referrer']);
  // if google then remove the rest because google doesn't have an info in q= any more.
  if(strpos($ref, 'google') !== false) {
    $ref = preg_replace("~\?.*$~", '', $ref);
  }
  $row['referrer'] = $ref;
}

$sql = "select sec_to_time(sum(difftime)/count(*)) from tracker where endtime!='' && hour(difftime)=0";
$S->query($sql);
list($av) = $S->fetchrow('num');

$sql = "select page, ip, agent, starttime, endtime, difftime, referrer ".
       "from tracker where starttime > date_sub(now(), interval 3 day) order by starttime desc";

list($tracker) = $T->maketable($sql, array('callback'=>callback,
                                           'attr'=>array('id'=>'tracker', 'border'=>'1')));

echo <<<EOF
$top
<p>This report is gethered once each hour. Results are limited to 20 records.</p>
$page
<h2>Tracker (real time)</h2>
<p>Click on IP to show only that IP.</p>
<p>Click on Page to show only that page.</p>
<p>Average stay time: $av (times over an hour are discarded.)</p>
<p>Showing only last 3 days.</p>
$tracker
$footer
EOF;
?>
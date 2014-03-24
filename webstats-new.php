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
  $("#blpmembers, #logagent, #memberpagecnt, #counter, #tracker").tablesorter()
    .addClass('tablesorter'); // attach class tablesorter to all except our counter

  // Tracker
  // Don't show webmaster

  var myIp = "$S->myIp";
  $("#tracker td:nth-child(2):contains('"+myIp+"')").parent().hide();

  // Show or Hide Robots function

  function showhidebots(type) {
    $("#tracker td:nth-child(3)").each(function(i, v) {
      var agent = $(v).text(), pat = new RegExp("bot|[+]*http|crawl", "i");
      if(pat.test(agent)) {
        if(type == "hide") {
          $(v).parent().hide();
        } else {
          $(v).parent().show().css("color", "red"); // Color robots red
        }
      }
    });
  }

  showhidebots('hide'); // Hide them at startup

  // Put a couple of buttons before the table

  $("#tracker").before("<div id='trackerselectdiv'>"+
                       "<button id='showhide'>Show Webmaster</button>"+
                       "<button id='showhidebots'>Show Robots</button>"+
                       "</div>");

  // ShwoHide Webmaster clicked

  $("#showhide").click(function(e) {
    if(this.flag) {
      // Hide Webmaster
      $("#tracker tr td:nth-child(2):contains("+myIp+")").parent().hide();
      $(this).text("Show Webmaster");
    } else {
      // Show all
      $("#tracker tr").show();
      if($("#showhiderobots").prop('flag') == false) {
        showhidebots('hide');
      }
      $(this).text("Hide Webmaster");
    }
    this.flag = !this.flag;
  });

  // IP address clicked

  $("#tracker td:nth-child(2)").click(function(e) {
    if(this.flag) {
      // show all
      $("#tracker tr").show();
      $("#showall").remove();
      $("#tracker td:nth-child(2):contains('"+myIp+"')").parent().hide();
      $("#showhide").prop('flag', false);
      $("#showhide").text("Show Webmaster").show();
    } else {
      // show only IP
      var ip = $(this).text();
      $("#tracker tr").hide();
      $("#tracker tr td:contains("+ip+")").parent().show();
      $("#tracker").before("<button id='showall'>Show All</button>");
      $("#showhide").hide();
      $("#showall").click(function(e) {
        $("#tracker tr").show();
        $(this).remove();
        $("#tracker td:nth-child(2):contains('"+myIp+"')").parent().hide();
        $("#showhide").prop('flag', false);
        $("#showhide").text("Show Webmaster").show();
      });
    }
    this.flag = !this.flag;
    return false;
  });

  // ShowHideBots clicked

  $("#showhidebots").click(function() {
    if(this.flag) {
      // hide
      showhidebots('hide');
      $("#showhidebots").text("Show Robots");
    } else {
      // show
      showhidebots('show');
      $("#showhidebots").text("Hide Robots");
    }
    this.flag = !this.flag;
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
  $ref = urldecode($row['referrer']);
  // if google then remove the rest because google doesn't have an info in q= any more.
  if(strpos($ref, 'google') !== false) {
    $ref = preg_replace("~\?.*$~", '', $ref);
  }
  $row['referrer'] = $ref;
}

$sql = "select page, ip, agent, starttime, endtime, difftime, referrer ".
       "from tracker order by starttime desc";

list($tracker) = $T->maketable($sql, array('callback'=>callback,
                                           'attr'=>array('id'=>'tracker', 'border'=>'1')));

echo <<<EOF
$top
<p>This report is gethered once each hour. Results are limited to 20 records.</p>
$page
<h2>Tracker (real time)</h2>
<p>Click on IP to show only that IP.</p>
$tracker
$footer
EOF;
?>
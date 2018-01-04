<?php
// Show only non-robots and non 0 isJavaScript from 'tracker'
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

// Get the country from the ipcountry tables.

if($ip = $_POST['ip']) {
  // Turn an ip into a long

  function Dot2LongIP($IPaddr) {
    if(strpos($IPaddr, ":") === false) {
      if($IPaddr == "") {
        return 0;
      } else {
        $ips = explode(".", "$IPaddr");
        return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
      }
    } else {
      //error_log("IPaddr: $IPaddr");
      $int = inet_pton($IPaddr);
      $bits = 15;
      $ipv6long = 0;

      while($bits >= 0) {
        $bin = sprintf("%08b", (ord($int[$bits])));
        if($ipv6long){
          $ipv6long = $bin . $ipv6long;
        } else {
          $ipv6long = $bin;
        }
        $bits--;
      }
      $ipv6long = gmp_strval(gmp_init($ipv6long, 2), 10);
      //error_log("ipv6long: $ipv6long");
      return $ipv6long;
    }
  }

  $iplong = Dot2LongIP($ip);

  if(strpos($ip, ":") === false) {
    $table = "ipcountry";
  } else {
    $table = "ipcountry6";
  }
  $sql = "select countryLONG from $S->masterdb.$table ".
          "where '$iplong' between ipFROM and ipTO";

  $S->query($sql);

  list($name) = $S->fetchrow('num');
  echo $name;
  exit();
}

if(!$_GET) {
  list($top, $footer) = $S->getPageTopBottom();
  echo <<<EOF
$top
<h1>Enter Site Name</h1>
<form method='get'>
<input type='text' name='site' autofocus><br>
<input type='submit' value="Submit">
</form>
$footer
EOF;
  exit();
}

$siteName = $_GET['site'];
  
// Select info from 'tracker'

$sql = "select ip, agent, page, lasttime, hex(isJavaScript), difftime ".
       "from barton.tracker where site='$siteName' ".
       "and isJavaScript & 0x2000 != 0x2000 ".
       "and isJavaScript != 0 and ip != '75.108.73.143' ".
       "and difftime > 60 order by lasttime";

$S->query($sql);

// Get the result as we will do other sql querys inside the while.

$r = $S->getResult();

$tbl = <<<EOF
<table border="1">
EOF;

while(list($ip, $agent, $filename, $lasttime, $isJava, $diff) = $S->fetchrow($r, 'num')) { // $r is result
  //$country = getCountry($ip);
  $min = $diff / 60;
  $hr = $min / 60;
  $min = $min % 60;
  $sec = $diff % 60;
  $strdiff = sprintf("%d", $hr) . ":" . sprintf("%02d",$min). ":" . sprintf("%02d", $sec);
  $tbl .= "<tr><td><span class='ip'>$ip</span> $filename <span class='country'>$country</span><br>".
          "$agent</td><td>$lasttime<br>$strdiff</td><td>$isJava</td></tr>";
}

$tbl .= "</table>";

$h->banner = "<h1>Show Non Bots from 'tracker'</h1>";
$h->css =<<<EOF
  <style>
td {
  padding: .2rem;
}
td:first-child {
  word-wrap: break-word;
  width: 75%;
}
.ip {
  background-color: lightgreen;
  padding: .2rem;
}
.country {
  background-color: pink;
  padding: .2rem;
  display: none;
}
  </style>
EOF;

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  var ipspan = $(".ip");
  ipspan.each(function(i) {
    var ip = $(this).text();
    var that = this;
    $.ajax("shownonbots.php", {
      data: {ip: ip},
      type: 'post',
      success: function(co) {
        $(that).next().text(co).show();
        return false;
      }
    });
  });

  var mouseflag = true;

  $("body").on("click", function(e) {
    if(mouseflag === false) {
      $("#Messages").remove();
      mouseflag = !mouseflag;
    }
  });

  $("body").on("click","table td:nth-child(3)", function(e) {
    if(mouseflag) {
      var js = parseInt($(this).text(), 16),
      human, h = '', ypos, xpos;

      // The td is in a tr which in in a tbody, so table is three
      // prents up.
      
      human = {
        1: "Start", 2: "Load", 4: "Script", 8: "Normal",
        0x10: "NoScript", 0x20: "B-PageHide", 0x40: "B-Unload", 0x80: "B-BeforeUnload",
        0x100: "T-BeforeUnload", 0x200: "T-Unload", 0x400: "T-PageHide",
        0x1000: "Timer", 0x2000: "Bot", 0x4000: "Csstest"
      };
      xpos = e.pageX - 200;

      ypos = e.pageY;

      if(js == 0) {
        h = 'curl';
      } else {
        for(var [k, v] of Object.entries(human)) {
          h += (js & k) ? v + "<br>" : '';
        }
      }

      $("#Messages").remove();
      $("body").append("<div id='Messages' style='position: absolute; top: "+ypos+"px; left: "+xpos+"px; "+
                       "background-color: white; border: 5px solid black; "+
                       "padding: 10px;'>"+h+"</div>");
    }
    e.stopPropagation();
    mouseflag = !mouseflag;
  });
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h4>$siteName</h4>
<p>For people who stayed more than one minute.</p>
$tbl
$footer
EOF;

<?php
// This is used by index.php instead of webstats-new.php. However, note that this file uses
// webstats-new.js which uses webstats-new.php for its AJAX calls!  
// BLP 2016-06-22 -- NOTE: this uses http://bartonphillips.net/js/webstats-new.js which in turn uses
// webstats-new.php for ALL OF IT AJAX calls!!!
// BLP 2016-06-13 -- This version of webstats let me select the site at the top and then does the
// rest. It does not use the webstat.i.txt but rather get everything fresh. It still uses the
// <site>-analysis.txt files however. The is only for bartonlp.com not conejoskiclub.org! so the
// file is just a simple php not an eval. This file is only in the 'bartonphillips' directory and
// only used in bartonphillips.com.
// BLP 2016-05-06 -- add get site to analysis
// BLP 2016-01-15 -- put this in http://bartonphillips.net/ and put simlinks in the other
// directories.  
// BLP 2016-01-06 -- add 'Show showall' to tracker
// BLP 2014-11-02 -- make tracker average stay reflect the current state of the table.
// BLP 2014-08-30 -- change $av to only look at last day and to allow only times less the 2hr.

$_site = require_once(getenv("SITELOAD")."/siteload.php");
$S = new $_site->className($_site);

$visitors = [];
$jsEnabled = [];
$ipcountry = '';

$S->siteDomain = $S->siteName;

// FORM POST and drop into main

if(isset($_POST['submit'])) {
  $siteName = $_POST['site'];

  $S->siteDomain = $S->siteName = $siteName;
}

// Main rendering

$page =  getwebstats($S);

$h->link = <<<EOF
  <!-- local css links -->
  <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
<!--[if lte IE 8]>
  <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/grids-responsive-old-ie-min.css">
<![endif]-->
<!--[if gt IE 8]><!-->
  <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/grids-responsive-min.css">
<!--<![endif]-->
  <link rel="stylesheet" href="http://bartonphillips.net/css/tablesorter.css">
  <link rel="stylesheet" href="http://bartonphillips.net/css/webstats-new.css">
EOF;

$h->css = <<<EOF
  <style>
* {
  box-sizing: border-box !important;
}
  </style>
EOF;

// Gather ipcountry information

$sql = "select distinct ip from $S->masterdb.tracker where site='$S->siteName' and starttime >= current_date() - interval 24 hour";

$S->query($sql);
$tkipar = array(); // tracker ip array

while(list($tkip) = $S->fetchrow('num')) {
  $tkipar[] = $tkip;
}
$tkipar = array_keys(array_flip($tkipar));

$list = json_encode($tkipar);

// Now we want to do a POST so set up the context first.

$options = array('http' => array(
                                 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                 'method'  => 'POST',
                                 'content' => http_build_query(array('list'=>$list))
                                )
                );

$context  = stream_context_create($options);

// Now this is going to do a POST!

$ipc = file_get_contents("http://www.bartonlp.com/webstats-new.php", false, $context);

foreach(json_decode($ipc) as $k=>$v) {
  $ipcountry[$k] = $v;
}

// BLP 2016-05-06 -- $jsonIpcountry etc. must happen after $ipcountry is filled!

$jsonIpcountry = json_encode($ipcountry);

$h->extra = <<<EOF
  <script>
var ipcountry = JSON.stringify($jsonIpcountry);
var thesite = "$S->siteName";
var myIp = "$S->myIp";
  </script>
  <script src="http://bartonphillips.net/js/tablesorter/jquery.tablesorter.js"></script>
  <script src="http://bartonphillips.net/js/webstats-new.js"></script>
EOF;

$h->title = "Web Statistics";
$sitename = strtolower($S->siteDomain);
$h->banner = "<h1 id='maintitle'>Web Stats For <b>$sitename</b></h1>";

list($S->top, $S->footer) = $S->getPageTopBottom($h);

// Render the page

$members = $S->memberTable ? "\n<li><a href='#table7a'>Goto Table: memberTable</a></li>" : '';

list($top, $footer) = $S->getPageTopBottom();

echo renderPage($S, $page);
exit();

function getwebstats($S) {
  global $visitors, $jsEnabled;
  
  $blpips = array();

  $T = new dbTables($S);

  $query = "select blpIp as 'BLP IP', createtime as Since from $S->masterdb.blpip order by createtime desc";

  list($tbl) = $T->maketable($query, array('callback'=>'blpipmake', 'attr'=>array('id'=>'blpid','border'=>"1")));

  if(!$tbl) {
    $tbl = "<h3 class='noNewData'>No New Data Today</h2>";
  }

  $creationDate = date("Y-m-d H:i:s T");

  $page = <<<EOF
<hr/>
</script>

<p id="hourly" class="h-update">The first six tables are created within an hour of this access.<br>
 This was created $creationDate. Hourly update.</p>

<h2>From table <i>blpip</i></h2>
<p>These are the IP Addresses used by the Webmaster. When these addresses appear in the other tables they are in
<span style="color: red">RED</span>.</p>
$tbl
EOF;

  $n = $S->query("select id from $S->masterdb.logagent where site='$S->siteName' and id!=0 and lasttime >= current_date() limit 1");

  $idfield = $n ? ", id as ID" : '';
  
  $query = "select ip as IP, agent as Agent$idfield, count as Count, lasttime as LastTime " .
  "from $S->masterdb.logagent where site='$S->siteName' and lasttime >= current_date() order by lasttime desc";

  list($tbl) = $T->maketable($query,
                             array('callback'=>'blpip',
                                   'attr'=>array('id'=>"logagent", 'border'=>"1")));
  if(!$tbl) {
    $tbl = "<h3 class='noNewData'>No New Data Today</h2>";
  }

  $page .= <<<EOF
<h2 id="table3">From table <i>logagent</i> for today</h2>
<a href="#table4">Next</a>
$tbl
EOF;

  // Here 'count' is total number of hits so count-realcnt is the number of Bots.
  
  $query = "select filename as Page, realcnt as 'Real', (count-realcnt) as 'Bots', lasttime as LastTime from $S->masterdb.counter ".
  "where site='$S->siteName' and lasttime >= current_date() order by lasttime desc";

  list($tbl) = $T->maketable($query, array('attr'=>array('border'=>'1', 'id'=>'counter')));

  if(!$tbl) {
    $tbl = "<h3 class='noNewData'>No New Data Today</h2>";
  }

  $page .= <<<EOF
<h2 id="table4">From table <i>counter</i> for today</h2>
<a href="#table5">Next</a>
<p>Shows the total number of hits for a page.<br>
'count' is the total number of hits including real and bots. 'real' is the number of non-bots.</p>
$tbl
EOF;

  $today = date("Y-m-d");

  // are there any members during this day
  
  $query = "select members from $S->masterdb.counter2 where site='$S->siteName' and members!=0 and lasttime >= current_date() limit 1";
  $memberquery = $S->query($query) ? "members as Members," : '';

  // 'count' is actually the number of 'Real' vs 'Bots'. A true 'count' would be Real + Bots.
  
  $query = "select filename as Page, count as 'Real',$memberquery bots as Bots, lasttime as LastTime ".
           "from $S->masterdb.counter2 ".
           "where site='$S->siteName' and lasttime >= current_date() order by lasttime desc";
  
  list($tbl) = $T->maketable($query, array('attr'=>array('border'=>'1', 'id'=>'counter2')));

  if(!$tbl) {
    $tbl = "<h3 class='noNewData'>No New Data Today</h2>";
  }

  $page .= <<<EOF
<h2 id="table5">From table <i>counter2</i> for today</h2>
<a href="#table6">Next</a>
<p>Shows the number of hits today for each page.<br>
$tbl
EOF;

  // Get the footer line
  
  $query = "select sum(`real`+bots) as Count, sum(`real`) as 'Real', sum(bots) as 'Bots', ".
           "sum(members) as 'Members', sum(visits) as Visits " .
           "from $S->masterdb.daycounts ".
           "where site='$S->siteName' and lasttime >= current_date() - interval 6 day";

  $S->query($query);
  list($Count, $Real, $Bots, $Members, $Visits) = $S->fetchrow('num');

  // Use 'tracker' to get the number of Visitors ie unique ip accesses.
  
  $S->query("select ip, date(lasttime) ".
            "from $S->masterdb.tracker where lasttime>=current_date() - interval 6 day and site='$S->siteName' ".
            "order by date(lasttime)");

  $Visitors = 0;

  // There should be ONE UNIQUE ip in the rows. So count them into the date.

  $tmp = '';
  
  while(list($ip, $date) = $S->fetchrow('num')) {
    $tmp[$date][$ip] = '';
  }

  foreach($tmp as $d=>$v) { 
    $visitors[$d] = $n = count($v);
    $Visitors += $n;
  }
  
  if($Members) {
    $memberfooter = "<th>$Members</th>";
    $memberquery = ", members";
  } else {
    $memberfooter = '';
    $memberquery = '';
  }

  $sql = "select count(*), date(starttime) from $S->masterdb.tracker ".
         "where date(starttime)>=current_date() - interval 6 day and site='$S->siteName' and ".
         "isJavaScript & ~(0x201c) and not (isJavaScript & 0x2000) group by date(starttime)  order by date(starttime)";
  
  $S->query($sql);

  $jsenabled = 0;
  
  while(list($cnt, $date) = $S->fetchrow('num')) {
    //echo "$cnt, $date<br>";
    $jsEnabled[$date] += $cnt;
    $jsenabled += $cnt;
  }

  $ftr = "<tr><th>Totals</th><th>$Visitors</th><th>$Count</th><th>$Real</th>".
         "<th>$jsenabled</th><th>$Bots</th>$memberfooter<th>$Visits</th></tr>";

  // Get the table lines
  
  $query = "select date as Date, 'visitors' as Visitors, `real`+bots as Count, `real` as 'Real', 'AJAX', ".
           "bots as 'Bots'$memberquery, visits as Visits ".
           "from $S->masterdb.daycounts where site='$S->siteName' and ".
           "lasttime >= current_date() - interval 6 day order by date desc";

  function visit(&$row, &$rowdesc) {
    global $visitors, $jsEnabled;

    $row['Visitors'] = $visitors[$row['Date']];
    $row['AJAX'] = $jsEnabled[$row['Date']];
    return false;
  }
  
  list($tbl) = $T->maketable($query, array('callback'=>'visit', 'footer'=>$ftr, 'attr'=>array('border'=>"1", 'id'=>"daycount")));

  if(!$tbl) {
    $tbl = "<h3 class='noNewData'>No New Data Today</h2>";
  }

  if(is_array($S->daycountwhat)) {
    $counting = implode(", ", $S->daycountwhat);
  } else {
    $counting = $S->daycountwhat ? $S->daycountwhat : 'All files';
  }
  if(strtolower($counting) == 'all') {
    $counting = "All files";
  }

  $next = $S->memberTable ? "#table7a" : "#table7";
    
  $page .= <<<EOF
<h2 id="table6">From table <i>daycount</i> for seven days</h2>
<p>'Visitors' is the number of distinct IP addresses (via 'tracker' table).<br>
'Count' is the sum of 'Real' and 'Bots', the total number of HITS.<br>
'Real' is the number of non-robots.<br>
'AJAX' is the number of non-robots with AJAX functioning (via 'tracker' table).<br>
'Bots' is the number of robots.<br>
'Visits' are hits outside of a 10 minutes interval.<br>
So if you come to the site from two different IP addresses you would be two 'Visitors'.<br>
If you hit our site 10 times the sum of 'Real' and 'Bots' would be 10.<br>
If you hit our site 5 time within 10 minutes you will have only one 'Visits'.<br>
If you hit our site again after 10 minutes you would have two 'Visits'.</p>
<a href="$next">Next</a>
<p>Counting $counting.</p>
$tbl
EOF;

  if($S->memberTable) {
    $query = "select * from memberpagecnt where lasttime >= current_date() - interval 7 day";
    list($tbl) = $T->maketable($query, array('attr'=>array('border'=>"1", 'id'=>"memberpagecnt")));

    $page .= <<<EOF
<h2 id="table7a">Table <i>memberpagecnt</i> for seven days</h2>
<a href="#table7">Next</a>
$tbl
EOF;
  }

  $page .= "<p class='h-update'>End Hourly update.</p>";

  // Write the file out

  file_put_contents("webstats.i.txt", $page);

  return $page;
}

// Call back functions

// fill the $blpips array with the ip numbers

function blpipmake(&$row, &$rowdesc) {
  global $blpips;
  $blpips[$row['BLP IP']] = 1;

  return false;
}

// If the ip address is in the $blpips array make the ip row say BARTON in red.

function blpip(&$row, &$rowdesc) {
  $blpips = $GLOBALS['blpips'];
  
  if($blpips[$row['IP']]) {
    $row['IP'] = "<span class='blp-row'>{$row['IP']}</span>";
  }

  $row['Agent'] = escapeltgt($row['Agent']);

  return false;
}

// Display the page with the $page. $page has the make-webstats.php stuff

function renderPage($S, $page) {
  global $ipcountry;
  
  // The analysis files are updated once a day by a cron job.
  $T = new dbTables($S);

  $analysis = file_get_contents("http://bartonphillips.net/analysis/$S->siteName-analysis.i.txt");
  if(!$analysis) $errMsg = "http://bartonphillips.net/analysis/$S->siteName-analysis.i.txt: NOT FOUND";

  function trackerCallback(&$row, &$desc) {
    global $S, $ipcountry;

    $ip = $S->escape($row['ip']);

    $co = $ipcountry[$ip];

    $row['ip'] = "<span class='co-ip'>$ip</span><br><div class='country'>$co</div>";

    if(($row['js'] & 0x2000) === 0x2000) {
      $desc = preg_replace("~<tr>~", "<tr class='bots'>", $desc);
    }
    $row['js'] = dechex($row['js']);
  }

  $sql = "select ip, page, agent, starttime, endtime, difftime, isJavaScript as js ".
         "from $S->masterdb.tracker where site='$S->siteName' and starttime >= current_date() - interval 24 hour ". 
         "order by starttime desc";

  list($tracker) = $T->maketable($sql, array('callback'=>'trackerCallback',
                                              'attr'=>array('id'=>'tracker',
                                              'border'=>'1')));

  $sql = "select ip, agent, count, hex(robots) as bots, who, creation_time as 'created', lasttime ".
         "from $S->masterdb.bots ".
         "where lasttime >= current_date() and count !=0 order by lasttime desc";

  list($bots) = $T->maketable($sql, array('attr'=>array('id'=>'robots', 'border'=>'1')));

  $sql = "select ip, agent, site, which, count from $S->masterdb.bots2 ".
         "where date >= current_date() order by lasttime desc";

  list($bots2) = $T->maketable($sql, array('attr'=>array('id'=>'robots2', 'border'=>'1')));

  // figure out the timezone of the server by doing 'date' which returns
  // something like: Sun Dec 28 12:14:44 MST 2014
  // Get the first letter of the time zone, like M for MST etc.

  $date = date("Y-m-d H:i:s T");



 $ret = <<<EOF
$S->top
$errMsg
<form action="webstats.php" method="post">
  Select Site:
  <select name='site'>
    <option>Applitec</option>
    <option>Allnatural</option>
    <option>Bartonlp</option>
    <option>BartonlpOrg</option>
    <option>Bartonphillips</option>
    <option>GranbyRotary</option>
    <option>Messiah</option>
    <option>Puppiesnmore</option>
    <option>Weewx</option>
  </select>

  <button type="submit" name='submit'>Submit</button>
</form>
<main>
<p>$date</p>
<ul>
   <li><a href="#table3">Goto Table: logagent</a></li>
   <li><a href="#table4">Goto Table: counter</a></li>
   <li><a href="#table5">Goto Table: counter2</a></li>
   <li><a href="#table6">Goto Table: daycounts</a></li>$members
   <li><a href="#table7">Goto Table: tracker</a></li>
   <li><a href="#table8">Goto Table: bots</a></li>
   <li><a href="#table9">Goto Table: bots2</a></li>
   <li><a href="#analysis-info">Goto Analysis Info</a></li>
</ul>

<div id="hourly-update">
$page
</div>

<h2 id="table7">Table Seven from table <i>tracker</i> (real time) for last 24 hours</h2>
<a href="#table8">Next</a>
<p>'js' is hex. 1, 2, 32(x20), 64(x40), 128(x80, 256(x100), 512(x200) and 4096(x1000) are done via 'ajax'.<br>
4, 8 and 16(x10) via an &lt;img&gt;<br>
1=start, 2=load, 4=script, 8=normal, 16(x10)=noscript,<br>
32(x20)=beacon/pagehide, 64(x40)=beacon/unload, 128(x80)=beacon/beforeunload,<br>
256(x100)=tracker/beforeunload, 512(x200)=tracker/unload, 1024(x400)=tracker/pagehide,<br>
4096(x1000)=tracker/timer: hits once every 5 seconds via ajax.</br>
8192(x2000)=SiteClass (PHP) determined this is a robot via analysis of user agent or scan of 'bots'.<br>
The 'starttime' is done by SiteClass (PHP) when the file is loaded.</p>
$tracker
<h2 id="table8">Table Eight from table <i>bots</i> (real time) for Today</h2>
<a href="#table9">Next</a>
<p>The 'bots' field is hex.<br>
The 'count' field is the total count since 'created'.<br>
From 'rotots.txt': Initial Insert=1, Update= OR 2.<br>
From app scan: Initial Insert=4, Update= OR 8.<br>
From 'Sitemap.xml': Initial Insert=16(x10), Update= OR 32(x20).<br>
From 'tracker' cron: Inital Insert=64(x40), Update= OR 128(x80).<br>
So if you have a 1 you can't have a 4 and visa versa.</p>
$bots
<h2 id="table9">Table Nine from table <i>bots2</i> (real time) for Today</h2>
<a href="#analysis-info">Next</a>
<p>'which' is 1 for 'robots.txt', 2 for the application, 4 for 'Sitemap.xml'.<br>
The 'count' field is the number of hits today.</p>
$bots2
<div id="analysis">
$analysis
</div>
<hr>
</main>
$S->footer
EOF;

  return $ret;
}
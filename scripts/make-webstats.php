#! /usr/bin/php -q
<?php
// This is CLI and run as a cron job to aggregate the web statistics for a day.
// The results are used by the webstats.php program in our document root directory.

define('TOPFILE', "/home/barton11/includes/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

// Open the database

Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

$S = new Database($dbinfo);

$limit = " limit 20";
$date = date("Y-m-d H:i:s");

$blpips = array();

$t = new dbTables($S);

$query = "select blpIp as 'BLP IP', createtime as Since from blpip order by createtime desc";

list($tbl) = $t->maketable($query, array('callback'=>blpipmake, 'attr'=>array('border'=>"1")));

$page = <<<EOF
<hr/>
<h2>$date San Diego Time</h2>
<div id="blpip">
<p>These are the IP Addresses used by the Webmaster. When these addresses appear in the other tables they are in
<span style="color: red">RED</span>. Some of these are from the Granby Library (63.238.70.*)</p>
$tbl
</div>
<ul>
   <li><a href="#table2">Goto Table Two: ip, agent</a></li>
   <li><a href="#table3">Goto Table Three: memberpagecnt</a></li>
   <li><a href="#table4">Goto Table Four: counter</a></li>
   <li><a href="#table5">Goto Table Five: counter2</a></li>
   <li><a href="#table6">Goto Table Six: daycounts</a></li>
</ul>   

EOF;

$S->query("select count(*) as num, sum(count) as visits from logip");
$row = $S->fetchrow('assoc');
$ftr = "<tr><th colspan='3'>Total Records: {$row['num']}, Total Visits: {$row['visits']}</th></tr>\n";

$query = "select ip as IP, sum(count) as Count, lasttime as LastTime from logip ".
         "group by ip order by lasttime desc$limit";

list($tbl) = $t->maketable($query,
                           array('callback'=>'blpip',
                                 'attr'=>array('id'=>"blpmembers",
                                               'border'=>"1"), 'footer'=>$ftr));

$page .= <<<EOF
<div id="table1">
<a href="#table2">Goto Table2</a>
<h2>Table One: from table <i>logip</i></h2>
$tbl
</div>

EOF;

//$query = "select agent.ip as IP, agent as Agent, sum(agent.count) as Count, agent.lasttime as LastTime " .
//         "from logagent as agent left join logip as ip on agent.ip=ip.ip group by agent order by lasttime desc$limit";

$query = "select ip as IP, agent as Agent, count as Count, lasttime as LastTime " .
         "from logagent where lasttime > curdate() order by lasttime desc$limit";

list($tbl) = $t->maketable($query,
                           array('callback'=>'blpip',
                                 'attr'=>array('id'=>"logagent", 'border'=>"1")));

$page .= <<<EOF
<div id="table2">
<a name="table2"></a>
<a href="#table3">Goto Table3</a>
<h2>Table Two: from tables <i>logagent</i>. Data for today only.</h2>
$tbl
</div>

EOF;

$query = <<<EOF
select page as Page, agent as Agent, id as ID, ip as IP, count as Count, lasttime as LastTime
from memberpagecnt order by lasttime desc$limit
EOF;

list($tbl) = $t->maketable($query, array('callback'=>'blpip',
                                         'attr'=>array('border'=>"1", 'id'=>"memberpagecnt")));

$page .= <<<EOF
<div id="table3">
<a name="table3"></a>
<a href="#table4">Goto Table4</a>
<h2>Table Three: from table <i>memberpagecnt</i></h2>
<p>The member IDs are for the POKER CLUB members.</p>
$tbl
</div>

EOF;

$query = <<<EOF
select filename as Page, count as Count, lasttime as LastTime 
from counter order by lasttime desc$limit
EOF;
list($tbl) = $t->maketable($query, array('attr'=>array('border'=>'1', 'id'=>'counter')));

$page .= <<<EOF
<div id="table4">
<a name="table4"></a>
<a href="#table5">Goto Table5</a>
<h2>Table Four: from table <i>counter</i></h2>
$tbl
</div>

EOF;

$query = <<<EOF
select date as Date, filename as Page, count as Count, lasttime as LastTime 
from counter2 where lasttime > date_sub(now(), interval 7 day) order by date desc
EOF;
list($tbl) = $t->maketable($query, array('attr'=>array('border'=>'1', 'id'=>'counter2')));

$page .= <<<EOF
<div id="table5">
<a name="table5"></a>
<a href="#table6">Goto Table6</a>
<h2>Table Four: from table <i>counter2</i> for last 7 days</h2>
$tbl
</div>

EOF;

$query = "select count(*) as Visitors, sum(count) as Count, sum(visits) as Visits from daycounts";
$S->query($query);
list($Visitors, $Count, $Visits) = $S->fetchrow();
$S->query("select date from daycounts order by date limit 1");
list($start) = $S->fetchrow();

$ftr = "<tr><th>Totals</th><th>$Visitors</th><th>$Count</th><th>$Visits</th></tr>";

$query = "select date as Date, count(*) as Visitors, sum(count) as Count, sum(visits) as Visits
from daycounts group by date order by date desc$limit";

list($tbl) = $t->maketable($query, array('footer'=>$ftr, 'attr'=>array('border'=>"1", 'id'=>"daycount")));

$page .= <<<EOF
<div id="table6">
<a name="table6"></a>
<h2>Day Counts</h2>
<p>Day Counts are for 'index.php" and do NOT include webmaster visits.<br>
$tbl
</div>

EOF;

// Write the file out
file_put_contents("/home/barton11/www/webstats.i.txt", $page);

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
    $row['IP'] = "<span style='color: red'>{$row['IP']}</span>";
  }

  $row['Agent'] = escapeltgt($row['Agent']);

  return false;
}
 
?>

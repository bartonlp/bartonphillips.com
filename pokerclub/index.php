<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$S = new PokerClub;

$h->extra = <<<EOF
  <!-- This is an extended regexp library that handles things like /s options etc.
       Very nice but over kill for this app -->
  <script type='text/javascript' src='/js/XRegExp.js'></script>

  <script>
jQuery(document).ready(function($) {
  $("#pokerflush").animate({ 
                             opacity: 1.0,
                             left: 100
                           }, {duration: 5000 });
  
  $("#reset").click(function() {
    $.get("poker.ajax.php", { reset: 'reset' }, function() {
      location.href="";
    });
  });
  
  $("input").change(function() {
    var id = this.name;
    var val = this.value;
     $.get("poker.ajax.php", { 'id': id, 'val': val } );
  });

  $("#memberstbl .name").click(function(e) {
    var data = $(this).parent().html();
    
    var newdata = data.replace(/style="display:none"/g, ''); 
    var pat = new XRegExp("<td>\s*Yes.*?</td>", "s"); // use linked in XRegExp instead of native RegExp!!!
    newdata = newdata.replace(pat, '');
    //alert(newdata);
    newdata = "<span style='color: red'>Click to close</span>\
                           <table>\
                           <thead>\
                           <tr>\
                           <th>Name</th>\
                           <th>Address</th>\
                           <th>Cell Phone</th>\
                           <th>Buss. Phone</th>\
                           <th>BDAY</th>\
                           <th>Can Play</th>\
                           <th>Home Phone</th>\
                           <th>Email</th>\
                           <th>Spouse</th>\
                           <th>Last Hosted</th>\
                           <th>Map</th>\
                           </tr>\
                           </thead>\
                           <tbody>\
                           <tr>" + newdata + "</tr>\
                           </tbody>\
                           </table>";

    var \$div = $('#popup');

    \$div.html(newdata);
    var y = e.pageY;
    \$div.css( {top: y+20, left: 0});
    \$div.show();
  });

  $("#popup").click(function() {
    $(this).hide();
  });
});
  </script>

  <!-- Inline CSS if any -->
  <style>
body {
        background-color: lightblue;
}
#header {
        margin: 0 auto;
        width: 20%;
        height: 210px;
}
.extra {
        color: white;
        background-color: blue;
}
#memberstbl {
        border: 1px solid black;
        background-color: white;
}
#memberstbl th, #memberstbl td {
        padding: 4px;
        border: 1px solid black;
}
.map {
        color: white;
        background-color: blue;
}
#popup td, #popup th {
        border: 1px solid black;
}
#todayGuests, #todayGuests * {
        background-color: white;
        border: 1px solid black;
}
#todayGuests * {
        padding: 5px;
}
#wrapper {
}
#left {
        float: left;        
}
#right {
        float: right; margin-right: 50px;
}
  </style>
EOF;

$footer = $S->getFooter();

if($id = $_GET['id']) {
  $S->setIdCookie($id); // SetIdCookie is subclassed in PokerClub.class.php to use PokerClub as cookie
  $S->checkId();
}

// Post who is hosting information

if($_POST['page'] == 'post') {
  // post in table
  // echo "POST<br>";
  extract($_POST);
  $query = "insert into whoishosting (id, host, date) values(1, '$host', '$date') ".
           "on duplicate key update host='$host', date='$date'";
  $S->query($query);

  // also update the last hosted field in pokermembers
  $S->query("update pokermembers set lasthosted='$date' where concat(FName, ' ', LName)='$host'");
}

// Who is hosting page selected from main page

if($_GET['page'] == 'whoishosting') {
  // Edit who is hosting
  $h->title = "Poker Club";
  $h->banner = "<h1>Who Is Hosting?</h1>";
  $h->extra = <<<EOF
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.min.css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="/js/jquery-1.10.4.datepicker.min.js"></script>
<script>
jQuery(document).ready(function($) {
  $("#datepicker").datepicker({
    dateFormat: "yy-mm-dd"
  });
});
</script>
EOF;

  $top = $S->getPageTop($h);

  $n = $S->query("select concat(FName, ' ', LName) from pokermembers");

  $hosts = "<option>OPEN</option>\n";
  
  while(list($host) = $S->fetchrow('num')) {
    $hosts .= "<option>$host</option>\n";
  }

  echo <<<EOF
$top
<form action="$S->self" method="post">
<table>
<tr><th>Host Name</th><td>
<select name="host">
$hosts
</select></td></tr>
<tr><th>Date</th><td><input id='datepicker' type="text" name="date" /> (yyyy-mm-dd)</td></tr>
<tr><th celspan="2"><input type="submit" value="Submit" /></th></tr>
</table>
<input type="hidden" name="page" value="post"/>
</form>
$footer
EOF;
  exit();
}

//------------------------------------------
// UPDATE $WhoIsHosting

$user = $S->getUser();
$n = $S->query("select * from whoishosting");
if($n) {
  $row = $S->fetchrow('assoc');
  extract($row);
  if($host == "OPEN") {
    $who = "No one signed up yet";
  } else {
    $who = "at $host's Home";
  }
  $hostinfo = <<<EOF
<div style='text-align: center'>
<h3>Poker Night for {$date} $who</h3>
<p>You can sign up now by checking <b>Yes</b> or <b>No</b> below to let us know
   if you can attend or not.</p>
</div>
EOF;
} else {
  $hostinfo = <<<EOF
<p>No one has signed up for the next poker night.
To sign up <a href="$S->self?page=whoishosting">Click Here</a>
</p>
EOF;
}

$WhoIsHosting = <<<EOF
<h2 style='text-align: center;'>Welcome $user</h2>
$hostinfo
EOF;

//------------------------------------------

$h->title = "Poker Club";
$h->banner = "";

$top = $S->getPageTop($h);


echo <<<EOF
$top
<!-- popup div -->
<div id='popup' style='background-color: white; position: absolute; display: none; border: 1px solid black'></div>

<!-- Check to see if visitor is signed in -->

EOF;

if(!$S->id) {
  echo <<<EOF
<p>You are not logged in. Please <a href='login.php'>login</a> now.</p>
</body>
</html>
EOF;
   exit;
} else {
   $S->query("select Email, address, bphone, cphone, bday from pokermembers where id='$S->id'");
   $row = $S->fetchrow('assoc');
   if(!$row) {
     $S->setIdCookie(0);  // SetIdCookie is subclassed in PokerClub.class.php to use PokerClub as cookie
     echo "<h2>Invalid id. There is not member with id=$S->id</h2><p>Contact the webmaster at bartonphillips@gmail.com.</p>";
     exit();
   }
   extract($row);
   if($address || $bphone || $cphone || $bday) {
      $msg = "<div><a href='updateprofile.php'>Edit Your Profile</a></div>";
   } else {
      $msg = "<p style='text-align: center'><a href='updateprofile.php'>Please update your profile information, it does not look like you have done that yet.</a></p>";
   }

   // Welcome and Who Is Hosting Next Game
   echo <<<EOF
$WhoIsHosting
$msg

EOF;
}

$S->query("select * from pokermembers order by lasthosted desc");

$cnt = array(0, 0, 0, 0);

while($row = $S->fetchrow('assoc')) {
  foreach($row as $k=>$v) {
    if(isset($v)) {
      $$k = $v;
    } else {
      $$k = "&nbsp;";
    }
  }

  $name = "$FName $LName";
  
  $checked = array('','','','');

  //echo "id=$id, canplay=$canplay<br>";
  
  switch($canplay) {
    case 'yes':
      $checked[0] = 'checked';
      $cnt[0]++;
      break;
    case 'no':
      $checked[1] = 'checked';
      $cnt[1]++;
      break;
    case 'called':
      $checked[2] = 'checked';
      $cnt[2]++;
      break;
    case 'emailed':
      $checked[3] = 'checked';
      $cnt[3]++;
      break;
  }

  if($lat != "&nbsp;" && $lon != "&nbsp;") {
    $map = "<a  class='map' target='_blank' href='http://maps.google.com/maps?hl=en&q=$lat%20$lon'>Map</a>";
  } else {
    $map = "&nbsp;";
  }
  if($Email != "&nbsp;") {
    $email = "<a href='mailto:$Email'>$Email</a>";
  } else {
    $email = "&nbsp;";
  }
           
  $tbl .= <<<EOF
<tr>
<td>Yes <input type="radio" name="$id" value="yes" $checked[0]/>
 No <input type="radio" name="$id" value="no" $checked[1]/>
 Called <input type="radio" name="$id" value="called" $checked[2]/>
 Emailed <input type="radio" name="$id" value="emailed" $checked[3]/>
</td>
<td class="name">$name</td>
<td style="display:none">$address</td>
<td style="display:none">$cphone</td>
<td style="display:none">$bphone</td>
<td style="display:none">$bday</td>
<td style="display:none">$canplay</td>
<td>$hphone</td>
<td style="background-color: white">$email</td>
<td>$spouse</td>
<td>$lasthosted</td>
<td>$map</td>
</tr>

EOF;
}

if($S->id == ID_BARTON) {
  $blp = <<<EOF
<button id='reset'>Barton's Reset</button><br>
<a href="index.php?page=whoishosting">Host Poker</a>
EOF;
}

echo <<<EOF
<div>
   <a href="members.php">Members Page</a><br/>
   <a href="pokerinvite.php?date={$date}">Invite Member to a Poker Night</a><br/>
</div>

<!-- Body items go here -->
<div id='wrapper'>
<p>The <b>Can Play Info</b> is updated in the database from this
page and from the <b>Invitations</b> you can send with the above
link. You can use the <i>Radio Buttons</i> below to keep track
of who has been called and who has responded <i>Yes</i> or
<i>No</i> or who has been called but has not returned your call yet (<i>Called</i>).</p>

<table id='memberstbl'>
<thead>
<tr><th colspan='7'>Poker Club Members</th></tr>
<tr><th>Can Play Info</th><th>Name</th><th>Home
Phone</th><th>Email</th><th>Spouse</th><th>Last Hosted</th><th>Map</th></tr>
</thead>
<tbody>
$tbl
</tbody>
</table>
<p>Yes=$cnt[0], No=$cnt[1], Called=$cnt[2], Emailed but no reply=$cnt[3]</p>
$blp
</div>
<hr/>
<p style='text-align: center'><a href='aboutwebsite.php'>About This Site</a></p>
$footer
EOF;
?>


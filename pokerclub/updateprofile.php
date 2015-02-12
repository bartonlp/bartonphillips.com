<?php
require_once("/var/www/includes/siteautoload.class.php");
$S = new PokerClub;

$h->extra = <<<EOF
  <script>
jQuery.noConflict();

jQuery(document).ready(function($) {
  $("#pokerflush").animate({ 
opacity: 1.0,
left: 100
  }, {duration: 5000 });
});
   </script>

   <style type="text/css">
body {
        background-color: lightblue;
}
#header {
        margin: 0 auto;
        width: 20%;
        height: 210px;
}
#sendmail {
        color: red;
}
#bdayTable {
        width: 100%;
}
#bdayTable select {
        width: 100%;
        font-weight: bold;
        font-size: 12pt;
}
#bdayTable th {
        width: 20%;
        text-align: left;
}
#formDiv {
        width: 40%;
        margin-left: auto;
        margin-right: auto;
}
#formDiv h3 {
        text-align: center;
}
#submit {
        border: 1px solid black;
        width: 30%;
        margin-left: auto;
        margin-right: auto;
}
#submit input {
        background-color: yellow;
        width: 100%;
        font-size: large;
}
#userInfo {
        width: 100%;
}
#userInfo,
#userInfo th,
#userInfo td {
        background-color: white;
        border: 1px solid black;
}
#userInfo input {
        width: 96%;
}
#userInfo th {
        padding-left: 1em;
        padding-right: 1em;
#note {
        color: red;
        font-size: 80%;
        font-style: italic;
}
   </style>
EOF;

$h->title = "Edit Profile";
$h->banner = "<h2 style='text-align: center'>Edit Contact Information</h2>";
list($top, $footer) = $S->getPageTopBottom($h, "<a href='/pokerclub/'>Return to Main Page</a><hr>");

// Form submitted for update of database

if($_POST['submit'] == 'Submit') {
  // This is a bit of a hack but I want unset items to be null not ''
  $b_yr = $_POST['b_yr'];
  $b_mo = $_POST['b_mo'];
  $b_day = $_POST['b_day'];
  unset($_POST['b_yr']);
  unset($_POST['b_mo']);
  unset($_POST['b_day']);

  $bday = "$b_yr-$b_mo-$b_day";
  $_POST['bday'] = $bday;
  $id = $_POST['id'];
  unset($_POST['id']);
  unset($_POST['submit']);
  
  $set = " set ";
  foreach($_POST as $k=>$v) {
    if(empty($v)) {
      $set .= "$k=null,";
    } else {
      $set .= "$k='$v',";
    }
  }

  $set = rtrim($set, ',');
  $sql = "update pokermembers $set where id='$id'";
  $S->query($sql);

  echo <<<EOF
$top
<h3 style='text-align: center'>Database Updated</h3>
<hr/>
<a href='index.php'>Return to Home page</a>
$footer
EOF;
  exit();
}

if(!$_GET['id'] && $S->id == ID_BARTON) {
  // Show all of the entries and let me edit any of them

  echo <<<EOF
$top
<table border="1" style="font-size: small">
<thead>
<tr>
EOF;

  $S->query("select * from pokermembers");
  $result = $S->getResult();
  while($info = mysqli_fetch_field($result)) {
    echo "<th>$info->name</th>";
  }

  echo <<<EOF
</tr>
</thead>
<tbody>

EOF;

  while($row = $S->fetchrow('assoc')) {
    echo "<tr>\n";
    foreach($row as $key=>$value) {
      if($key == "id") {
        echo "<td><a href='$S->self?id=$value'>$value</a></td>\n";
      } else {
        echo "<td>$value</td>\n";
      }
    }
    echo "</tr>\n";
  }
  echo <<<EOF
</tbody>
</table>
$footer
EOF;
  exit();
}

// Not update so get info and display it for the user

if(!($id = $_GET['id'])) {
  $id = $S->id;
}

$cnt = $S->query("select * from pokermembers where id='$id'");

// How many Active members do we have

if($cnt != 1) {
  if($S->id == 0) {
    echo "<p>You have not logged in yet. Please <a href='login.php'>Login</a></p>";
  } else {
    echo "<p>Internal Error: id=$S->id, but count is $cnt not 1</p>";
  }
  exit();
}

$row = $S->fetchrow('assoc');
extract($row);
echo <<<EOF
$top
<div id='formDiv'>
<h3>Contact Information for $FName $LName</h3>
<form action="$S->self" method='post'>
<table id='userInfo'>
<tr>
<th>Name</th><th>$FName $LName</th>
</tr><tr>
<th>Username:</th><td><input type='text' name='username' value='$username'></td>
</tr><tr>
<th>Email:</th><td><input type='text' name='email' value='$Email'></td>
</tr><tr>
<th>Address:</th><td><input type='text' name='address' value='$address'></td>
</tr><tr>
<th>Home Phone:</th><td><input type='text' name='hphone' value='$hphone'></td>
</tr><tr>
<th>Work Phone:</th><td><input type='text' name='bphone' value='$bphone'></td>
</tr><tr>
<th>Cell Phone:</th><td><input type='text' name='cphone' value='$cphone'></td>
</tr><tr>
</tr><tr>
<th>Spouse:</th><td><input type='text' name='spouse' value='$spouse'></td>
</tr><tr>
<th>Last Hosted:</th><td><input type'text' name='lasthosted' value='$lasthosted'></td>
</tr><tr>
<th>Birthday:</th><td>
<table id='bdayTable'>
   <tr><th>Month:</th><td><select name='b_mo'>

EOF;

$l = split(",", "January,February,March,April,May,June,July,August,September,October,November,December");
list($y, $m, $d) = split("-", $row['bday']);

foreach ($l as $k=>$mo) {
  $i=$k+1;
  echo "<option value='$i'" . (($m == $i) ? " selected" : "") . ">$mo</option>\n";
}

echo <<<EOF
</select></td>
</tr><tr><th>Day:</th><td><select name='b_day'>

EOF;

for($i=1; $i < 32; ++$i) {
  echo "<option value='$i'" . (($d == $i) ? " selected" : "") . ">$i</option>\n";
}

echo <<<EOF
</select></td>
</tr><tr><th>Year:</th><td><select name='b_yr'>

EOF;

for($i=1910; $i < 2001; ++$i) {
  echo "<option value='$i'" . (($y == $i) ? " selected" : "") . ">$i</option>\n";
};

echo <<<EOF
</select></td>
</tr>
</table>
</td>
</tr><tr>
<th>Password:</th><td><input type='text' name='password' value='$password'></td>
</tr>
<tr><th>Latitude</th><td><input type='text' name='lat' value='$lat'></td></tr>
<tr><th>Longitude</th><td><input type='text' name='lon' value='$lon'></td></tr>

</table>
<input type="hidden" name="id" value="$id">
<div id='submit'><input type='submit' name='submit' value='Submit'></div>
</form>
</div>
<hr/>
$footer
EOF;

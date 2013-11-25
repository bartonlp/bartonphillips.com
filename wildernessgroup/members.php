<?php
require_once("../blp.i.php");
$B = new Blp;
$headTitle ="Wilderness Group Members";
include("header.i.php");

?>

<script type='text/javascript'>
$(document).ready(function() {
  $(".select").show();
  
  $("#selectAll").click( function() {
    $("input[type='checkbox']").attr("checked","true");
  });
  $("#selectNone").click( function() {
    $("input[type='checkbox']").removeAttr("checked");
  });
});
</script>  
   
   <!-- Styles for this page only -->
   <style type='text/css'>
#header {
        margin-bottom: 20px;
        text-align: center;
}
   </style>

</head>

<body>

<div id="header">
   <h1>Grand County Wilderness Group Members</h1>
   <a href="http://www.gcwg.org"><img
   src="http://www.gcwg.org/images/emblem4.jpg" alt="logo" width="150" /></a>
</div>
<hr/>
<?php
echo <<<EOF
<div style='margin-bottom: 20px'>
<p style='border: 1px solid black; display: table-cell; padding: 15px;'>
<span style='color: red'>To send an email to the member click on his
<b style='color: blue;'>Name</b></span>.
<br/>Or to send multiple emails check the names and use<br/>
the button at the bottom to submit the list.<br/>

Select: <span id='selectAll' class='select' style='color: blue; display: none'>All</span>,
<span id='selectNone' class='select' style='color: blue; display: none'>None</span>
</p>
</div>
EOF;

// Get all Active Members
// Active members

$result = $B->query("select * from wilderness order by lname");

// How many Active members do we have

$cnt = mysql_num_rows($result);

// Form for multiple emails

print("<form action='http://www.bartonphillips.com/wildernessgroup/multmail.php' method='post'>\n");

while($row = mysql_fetch_array($result)) {
  extract($row);

  print("<input type=checkbox name=Name[] value='$id'><a href='http://www.bartonphillips.com/wildernessgroup/email.php?id=$id'>$fname $lname</a><br/>\n");
}
echo <<<EOF
<br/><input type='submit' value='Send Emails'>
</form>
EOF;
?>

<hr/>

</body>
</html>

<?php
// Read in the config tile
$FILE = __FILE__;
require_once("member.config");

echo <<<EOF
$Header
<body>

$MainTitle

<p style='border: 1px solid black; display: table-cell; padding: 15px'>
<span style='color: red'>To send an email to the member click on his
   <b style='color: blue;'>Name</b></span>.

<br/>Or to send multiple emails check the names and use<br/>
the button at the bottom to submit the list.<br/>

Select: <span id='selectAll' class='select' style='color: blue; display: none'>All</span>,
<span id='selectNone' class='select' style='color: blue; display: none'>None</span>
</p>
EOF;

// Get all Active Members
// Active members

$y = MEMBER_QUERY;
eval ("\$x=\"$y\";");

$result = Query($x);

// How many Active members do we have

$cnt = mysql_num_rows($result);

// Form for multiple emails



echo <<<EOF
<form action='multmail.php' method='post'>
<p>Members ($cnt):<br/>
Name<br/>
</p>

<p>
EOF;

while($row = mysql_fetch_array($result)) {
  $checkbox = "<input type=checkbox name=Name[] value='$row[id]'>";

  print("<input type=checkbox name=Name[] value='$row[id]'><a href='email.php?id=$row[id]'>$row[FName] $row[LName]</a><br/>\n");
}

echo <<<EOF
</p>
<hr/>
<br/><input type='submit' value='Send Emails'>
</form>
</p>
EOF;

?>

<hr/>
<div class='blkcenter'>
<?php
$wc3val = <<<EOF
<!-- WC3 Validation for XHTML -->
<p>
   <a href="http://validator.w3.org/check?uri=referer"><img
   src="/images/valid-xhtml10.png"
   alt="Valid XHTML 1.0 Strict"
   style='height: 31px; width: 88px; border: 0'/></a>

   <a href="http://jigsaw.w3.org/css-validator/check/referer">
      <img style="border:0;width:88px;height:31px"
             src="http://jigsaw.w3.org/css-validator/images/vcss"
             alt="Valid CSS!" />
   </a>
</p>
$Footer
EOF;
?>
</div>


</body>
</html>

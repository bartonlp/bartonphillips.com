<?php
// BLP 2015-02-22 -- reworked to use http://bartonlp.com/email.php
// BLP 2014-09-16 -- update for barton11 on inmotionhosting
// Read in the config tile

require_once("/var/www/includes/siteautoload.class.php");

$S = new $siteinfo['className'];

$h->title = "Granby Monday Night Poker Club";
$h->banner = "<h1>Member's Page</h1>";
$h->css =<<<EOF
  <style>
input[type='submit'] {
  border-radius: 1em;
  padding: .5em;
  font-size: 1em;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Get all Active Members
// Active members

$sql = "select id, concat(FName, ' ', LName) from pokermembers ".
       "where Email is not NULL order by LName";

$cnt = $S->query($sql);

$members = '';

while(list($id, $name) = $S->fetchrow('num')) {
  $checkbox = "<input type=checkbox name=id[] value='$id'>";

   $members .=<<<EOF
<input type=checkbox name="id[]" value='$id'>
  <a href='email.php?id=$id&from=$S->id'>$name</a>
<br>
EOF;
}


echo <<<EOF
$top
<hr>
<p style='border: 1px solid black; display: table-cell; padding: 15px'>
<span style='color: red'>To send an email to the member click on his
   <b style='color: blue;'>Name</b></span>.

<br/>Or to send multiple emails check the names and use<br/>
the button at the bottom to submit the list.<br/>

Select: <span id='selectAll' class='select' style='color: blue; display: none'>All</span>,
<span id='selectNone' class='select' style='color: blue; display: none'>None</span>
</p>
<form action='email.php' method='post'>
<p>Members ($cnt):<br/>
Name<br/>
</p>
<p>
$members
<input type="hidden" name="from" value="$S->id">
</p>
<hr/>
<br/><input type='submit' value='Compose Emails'>
</form>
</p>
$footer
EOF;

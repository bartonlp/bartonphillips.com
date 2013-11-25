<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new PokerClub;

if($_POST['submit']) {
  // Post the mail
  extract($_POST); // date, message, Name[], reset

  if(empty($date)) {
    echo "<h1>NO DATE PROVIDED</h1>";
    exit();
  }

  $S->query("select concat(FName, ' ', LName), Email from pokermembers where id='$S->id'");
  list($sender, $senderEmail) = $S->fetchrow('num');

  if($reset) {
   $S->query("update pokermembers set canplay='reset'");
  }

  $urldate = urlencode($date);
  $msg = <<<EOF
Dear Poker Member,
We are trying to get a poker night together for $date. Please click on the appropriate reply below:
* I will attend  http://www.bartonphillips.com/pokerclub/respond.php?id=&attend=yes&date=$urldate 
* I can't attend  http://www.bartonphillips.com/pokerclub/respond.php?id=&attend=no&date=$urldate

***

EOF;
  $msg .= $message;

  $msg .= <<<EOF

---
Granby Poker Club
http://www.bartonphillips.com/pokerclub

EOF;

  $ids = "";
  
  for($i=0; $i < count($Name); ++$i) {
    $ids .= "$Name[$i],";
  }
  $ids = rtrim($ids, ',');

  $S->query("select id, concat(FName, ' ', LName), Email from pokermembers where id in ($ids)");

  while(list($id, $name, $email) = $S->fetchrow('num')) {
    // Put the correct id into the two respond URL's
    $sendMsg = preg_replace("/id=/", "id=$id", $msg);

    mail("\"$name\" <$email>", "Invitation to Poker Night", $sendMsg,
         "From: info@bartonphillips.com\r\n");

    $S->query("update pokermembers set canplay='emailed' where id='$id'");
  }
  $h->title = "Poker Invitatioon";
  $h->banner = "<h1>Your Invitations Have Been Sent</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  
  echo <<<EOF
$top
<a href="index.php">Return to Main Page</a>
$footer
EOF;
  exit();
}

$h->extra = <<<EOF
  <script src="http://www.granbyrotary.org/js/date_input/jquery.date_input.js"></script>
  <link rel="stylesheet" href="http://www.granbyrotary.org/js/date_input/date_input.css" type="text/css">
  
  <script>
jQuery(document).ready(function($) {
  $("#pokerflush").animate({ 
                             opacity: 1.0,
                             left: 100
                           }, {duration: 5000 });

  jQuery($.date_input.initialize);
  
  jQuery.extend(DateInput.DEFAULT_OPTS,
    {
      stringToDate: function(string) {
        var matches;
        if(matches = string.match(/^(\d{4,4})-(\d{2,2})-(\d{2,2})$/)) {
          return new Date(matches[1], matches[2] - 1, matches[3]);
        } else {
          return null;
        };
      },
      dateToString: function(date) {
        var month = (date.getMonth() + 1).toString();
        var dom = date.getDate().toString();
        if(month.length == 1)
          month = "0" + month;
        if(dom.length == 1)
          dom = "0" + dom;
        return date.getFullYear() + "-" + month + "-" + dom;
      }
    }
  );
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
  </style>
EOF;

$h->title = "Poker Invitation";
$h->banner = <<<EOF
<h1 style="text-align: center">Invite Members for the next Poker Night</h1>   
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>This page will send an invitation to all members that have email
   address in the database. You can select members to receive an
   invitation, the date of the poker night, and additional text you
   would like to include. An email will be sent with two links one for
   &quot;Yes I will attend&quot; and &quot;No I can't attend,
   Sorry&quot;. When the member clicks on the link in the email the
   target page will update the database. You can view the results on
   the home page. When this page sends the emails the database field
   that holds the <i>Yes</i> or <i>No</i> responses is reset.</p>

<form action="$S->self" method="post">
   <div style="width: 100%">
   <table border="1" cellpadding="1" cellspacing="0">
     <tr>
       <td>Date</td>
       <td><input class="date_input" type="text" name="date" value='{$_GET['date']}' /></td>
     </tr>
     <tr>
       <td width="100">Message.<br/>Like directions to your home etc.</td>
       <td style="width: 100%"><textarea id='inputmessage' name="message" style="width: 99%" rows="10"
            id="message"></textarea></td>
     </tr>
   </table>
   </div>
   <table border="1">
     <thead>
       <tr><th>Members to Invite</th><tr>
     </thead>
     <tbody>
EOF;

$S->query("select id, concat(FName, ' ', LName), Email from pokermembers where Email is not NULL");

while(list($id, $name, $Email) = $S->fetchrow('num')) {
  // display name with check box. All checked to start.
  echo <<<EOF
     <tr><td><input type="checkbox" name=Name[] value='$id' checked /> $name</td></tr>

EOF;
}

echo <<<EOF
   </tbody>
   </table>
   Reset Database <input type='checkbox' name="reset" checked /><br/>
   <input type='submit' name='submit' value='Send Invitations by Email'>
</form>
<hr/>
<p>The following member do not have email addresses in the database and must be contacted by phone.</p>
<table border="1">
<thead>
<tr><th>Name</th><th>Phone</th></tr>
</thead>
<tbody>
EOF;

$S->query("select id, concat(FName, ' ', LName), hphone from pokermembers where Email is NULL");

while(list($id,$name, $hphone) = $S->fetchrow('num')) {
  extract($row);
  echo <<<EOF
<tr><td>$name</td><td>$hphone</td></tr>

EOF;
}

echo <<<EOF
</table>
$footer
EOF;
?>
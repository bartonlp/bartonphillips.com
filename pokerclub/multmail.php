<?php
// BLP 2015-02-17 -- changed mail sending and removed attachments. Now using plain mail();

$FILE = basename(__FILE__);
require_once("/var/www/includes/siteautoload.class.php");;

$S = new GranbyRotary;
$referer = $_SERVER['HTTP_REFERER'];

// Don't let people come here from anywhere else than the members
// page! We can change this later to make it only our sites

if(!preg_match("(^http://www.granbyrotary\.org)i", $referer) && ($_REQUEST['mail'] != 1)) {
  if($referer) echo "referer=$referer<br/>";
  
  echo <<<EOL
<body>
<h1>This page can only be accessed from our members directory</h1>
<p>Please return to our <a href='index.php'>home page</a> and follow the <b>Members</b> link. </p>
</body></html>
EOL;

  exit;
}

$h->extra = <<<EOF
   <script type='text/javascript'>
jQuery(document).ready(function($) {
  $("#another").click(function() {
    $(this).before('<input name="userfile[]" type="file" /><br>');
  });
});
   </script>
EOF;
   
extract(stripSlashesDeep($_POST));

if(!empty($submit)) {
  // Submited message from this page

  $h->banner = "<h2>Mail Sent</h2>";
  list($top, $footer) =$S->getPageTopBottom($h);

  $S->query("select * from rotarymembers where id in ($ids)");

  $names = array();
  $sendIds = array();
  $cc = "";
  
  while($row = $S->fetchrow()) {
    extract($row);
    $cc .= "$FName $LName\n";
    $names["$FName $LName"] = $Email;
    $sendIds["$FName $LName"] = $id;
  }
    
  $uploads_dir = '/tmp';

  $crlf = "\n";

  while(list($key, $value) = each($names)) {
    $inx = 0;
    $msg = $message;

    $msg .= "\n\n--\ncc:\n$cc\n\nCourtesy of The Rotary Club of Granby\n";  

    mail("$key <$value>", $subject, $msg,
         "From: Granby Rotary Member Mail <info@granbyrotary.org>",
         "-fbartonphillips@gmail.com\r\n"
        );


  }

  // Log this email in the emails table
  // The table looks like this
  // id_fk: the members id
  // application: this is __FILE__ which will either be email.php or multmail.php
  // subject: the subject line
  // fromaddress: the from line
  // message: the email message
  // toaddress: $name <$email>. For multmail this is a comma seperated list of id's
  // sendtime: timestamp

  $mid = $S->id;
  $from =  $S->escape($S->email);
  $message = $S->escape($message);
  $subject = $S->escape($subject);
  
  $query = "insert into emails (id_fk,application,subject,fromaddress,message,toaddress) ".
           "value ('$mid', '$FILE', '$subject', '$from', '$message', '$ids')";

  $S->query($query);

  echo "$top$footer";
} else {
  // Called from members_direcgtory.php

  $h->banner = "<h2>Send Multiple Emails</h2>";
  $h->extra = <<<EOF
  <meta name="robots" content="noindex, nofollow">

  <!-- CSS for this page only -->
  <style>
#mailform * {
  font-size: 1.05em;
  background-color: white;
  padding: 1em;
}
#mailform table * {
  border: 1px solid black;
  cellpadding: 1px;
  cellspacing: 0px;
}
#mailform table input {
        width: 96%;
}
#mailform textarea {
        width: 96%;
        height: 10em;
}
#mailform table {
        width: 100%;
}
#mailform .left {
        text-align: left;
        width: 10px;
}
#mailform input[type='submit'] {
  border-radius: 1em;
  padding: .5em;
  margin-top: .5em;
}
  </style>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h);

  if(!count($Name)) {
    echo <<<EOF
$top
<h3 style="color: red">No one selected. Return and try again</h3>
$footer
EOF;
    exit();
  }

  $ids = '';
  for($i=0; $i < count($Name); ++$i) {
    $ids .= "$Name[$i],";
  }
  $ids = rtrim($ids, ',');

  $S->query("select * from rotarymembers where id in ($ids)");

  while($row = $S->fetchrow()) {
    extract($row);

    $names .= <<<EOF
<li>$FName $LName</li>
EOF;
  }

  echo <<<EOF
$top
<p>Sent Message to:
<ul>
$names
</ul>
</p>
<hr/>
<form id="mailform" action="$S->self" method="post">
  <table>
    <tr>
      <th class="left">Subject:</th>
      <td>
        <input autofocus required type="text" name="subject">
      </td>
    </tr>
    <tr>
      <th class="left">Message:</th>
      <td>
        <textarea required name="message"></textarea>
      </td>
    </tr>
  </table>
  <input type='submit' name='submit' value="Send Message">
  <input type='hidden' name='ids' value='$ids'>
</form>   
<hr/>
$footer
EOF;
}


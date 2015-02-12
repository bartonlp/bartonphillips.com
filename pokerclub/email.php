<?php
require_once("/var/www/includes/siteautoload.class.php");

$S = new PokerClub;

require_once("member.config");

$referer = $_SERVER['HTTP_REFERER'];

$self = $_SERVER['PHP_SELF'];

// Don't let people come here from anywhere else than the members
// page! We can change this later to make it only our sites

// allow google reader to redirect stuff from my rss feed.

if(!preg_match(ALLOWED_DOMAIN, $referer) && ($_REQUEST['mail'] != 1)) {
  if($referer) echo "referer=$referer<br/>";

  echo <<<EOL
<body>
<h1>This page can only be accessed from our members directory</h1>
</body></html>
EOL;

  exit;
}

// Load mail stuff now that we know we are OK

require_once('Mail.php');
require_once('Mail/mime.php');

echo <<<EOF
$Header
<body>
$MainTitle

EOF;

// Target for form action. This does the actual mailing if 'mail' is
// set to 1, otherwise show the form and get the message etc.

if($_POST['mail'] == 1) {
  extract(stripSlashesDeep($_POST));
  
  // Make sure that all the fields are filled in

  $m = "/^\s*$/"; // pattern to match -- line with nothing but white space
  
  if(preg_match($m, $name) || preg_match($m, $email)) {
    // This should not happen
    
    $PageTitle = "Internal ERROR, no 'name' or 'email'<br/>\n";
    $err = 1;
  }

  if(preg_match($m, $subject)) {
    $PageTitle .= "No 'Subject' supplied (click on back)<br/>\n";
    $err = 1;
  }

  if(preg_match($m, $message)) {
    $PageTitle .= "No 'Message' supplied (click on back)<br/>\n";
    $err = 1;
  }

  if(preg_match($m, $from)) {
    $PageTitle .= "No 'From' supplied (click on back)\n";
    $err = 1;
  }

  if(!isset($err)) {
    $PageTitle = "<h2 align='center'>Your message has been sent to<br/>$name</h2>\n";
  } 
  echo $PageTitle;

  if($err) {
    exit();
  }
  
  $uploads_dir = UPLOAD_DIR;

  $inx = 0;
  
  foreach ($_FILES["userfile"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $tmp_name = $_FILES["userfile"]["tmp_name"][$key];
      $filename = $_FILES["userfile"]["name"][$key];
      move_uploaded_file($tmp_name, "$uploads_dir/$filename");
      $file[$inx++] = "$uploads_dir/$filename";
    }
  }

  $crlf = "\n";
  $hdrs = array(
                'From'    => $from,
                'Subject' => $subject
               );

  $mime = new Mail_mime($crlf);

  $message .= MAIL_FOOTER;

  
  $mime->setTXTBody($message);
//  $mime->setHTMLBody($html);
  if(!empty($file)) {
    foreach($file as $value) {
      $mime->addAttachment($value);
    }
  }

//do not ever try to call these lines in reverse order
  $body = $mime->get();
  $hdrs = $mime->headers($hdrs);

  $mail =& Mail::factory('mail');
  echo "\n<pre class='debug'>$name <$email>, $hdrs, $body</pre>\n";

  $mail->send("$name <$email>", $hdrs, $body);

  if(!empty($file)) {
    foreach($file as $value) {
      unlink($value);
    }
  }
} else {
// Intial entry Display Form
  
// Get the id of the person we are sending the message to
// This is a GET

  $id = $_GET['id'];
  $subject = $_GET['subject'];

// This page can not be call directly!

  if(!isset($id)) {
    echo <<<EOF
<h2 id='head'>ERROR NO ID</h2>
<p>You can not use this page directly, it must be referenced from another page!</p>
</body>
</html>
EOF;

    exit;
  }

  $y = MAIL_QUERY;
  eval("\$x=\"$y\";");

  $S->query($x);

  $row = $S->fetchrow('assoc');

  $email = $row['Email'];
  $name = "$row[FName] $row[LName]";
  
  echo "<h2 id='head'>Send a message to $name</h2>\n";

  if($MemberId = GetMemberId()) {
    $y = GET_MEMBER;
    eval("\$x=\"$y\";");

    $S->query($x);

    $row = $S->fetchrow('assoc');
    $from = $row['Email'];
    $fromName = "$row[FName] $row[LName]";
  }

  echo <<<EOF
<form id='mailform' name='Email' method='post'
 enctype="multipart/form-data" action="$self">

   <table id='mailformtable' border="1" cellpadding="1" cellspacing="0">
      <tr>
         <td class='left'>From (email address)</td>
         <td><input class='inputtext' id='fromname' type='text' name='from' tabindex='1'
EOF;

  if(isset($from)) {
    echo <<<EOF
 value="$fromName<$from>"
EOF;
  }
  echo <<<EOF
/>
         </td>
         <tr>
            <td class='left'>Subject
            </td>
            <td><input class='inputtext" id='inputsubject' type='text' name='subject' value='$subject' tabindex='2' />
            </td>
         </tr>
         <tr>
            <td class='left'>Message
            </td>
            <td>
               <textarea id='inputmessage' name="message" cols="140" rows="10" tabindex='3'></textarea>
            </td>
         </tr>
   </table>
   <input type='hidden' name='email' value="$email">
   <input type='hidden' name='name' value="$name">
   <input type='hidden' name='mail' value='1'>

   <!-- Attachments -->

   <div id='attach' style='border: 1px solid black; width: 20%;'>
      Attachment:<br />
      <input name="userfile[]" type="file" /><br />
      <input id='attachbutton' type='button' value='Another Attachment' /><br/>
   </div>
   <br/>

   <input type='submit' name='submit' value='Send Mail' tabindex='4'>
</form>

<!-- script to focus on first element in form -->
<script type="text/javascript">
document.getElementById('fromname').focus();
</script>
<hr/>

EOF;
}

echo $Footer;
?>

</body>
</html>

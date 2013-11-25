
<?php
require_once("../blp.i.php");
$B = new Blp;

$headTitle = "Send Email to Member";
include("header.i.php");

$referer = $_SERVER['HTTP_REFERER'];

// Don't let people come here from anywhere else than the members
// page! We can change this later to make it only our sites

// allow google reader to redirect stuff from my rss feed.

if(!preg_match("(^http://www.bartonphillips\.(com|org)|http://www\.google.com/reader/view/\?source=gmailnonewmail)i", $referer) && ($_REQUEST['mail'] != 1)) {
  if($referer) echo "referer=$referer<br/>";
  
  echo <<<EOL
</head>
<body>
<h1>This page can only be accessed from our members directory</h1>
</body></html>
EOL;

  exit;
}
?>
   <script type="text/javascript">

jQuery(document).ready(function($) {
  $("#attach input[type='button']").click(function() {
    var file = $(".clone:first").clone().val("");
    $(this).before(file);
  });
});
   </script>

   <!-- CSS for this page only -->
   <style type='text/css'>
#mailform table input {
        width: 100%;
        border: 0;
        background-color: #FFC0CB; /* pink; */
}
#mailform textarea {
        width: 100%;
        border: 0;
        background-color: #FFC0CB; /* pink; */
}

#mailform table {
        width: 100%;
}
#mailform .left {
        text-align: left;
        width: 20%;
}
   </style>
   
</head>

<body>

<?php
require_once('Mail.php');
require_once('Mail/mime.php');

// Target for form action. This does the actual mailing if 'mail' is
// set to 1, otherwise show the form and get the message etc.

if($_REQUEST['mail'] == 1) {
  extract(stripSlashesDeep($_POST));

  // Make sure that all the fields are filled in

  $m = "/^\s*$/"; // pattern to match -- line with nothing but white space
  
  if(preg_match($m, $name) || preg_match($m, $email)) {
    // This should not happen
    
    $PageTitle = "Internal ERROR, no 'name' or 'email'<br/> ";
    $err = 1;
  }

  if(preg_match($m, $subject)) {
    $PageTitle .= "No 'Subject' supplied (click on back)<br/> ";
    $err = 1;
  }

  if(preg_match($m, $message)) {
    $PageTitle .= "No 'Message' supplied (click on back)<br/> ";
    $err = 1;
  }

  if(preg_match($m, $from)) {
    $PageTitle .= "No 'From' supplied (click on back)";
    $err = 1;
  }

  if(!isset($err)) {
    $PageTitle = "<h2 align='center'>Your message has been sent to<br/>$name</h2>";
  } else {
    $PageTitle = "<h2>$PageTitle</h2>";
  } 

  $uploads_dir = '/tmp';

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

  $message .= "\n\n--\nGrand County Wilderness Group\nhttp://www.gcwg.org/\n";

  
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
  $mail->send("$name <$email>", $hdrs, $body);

  if(!empty($file)) {
    foreach($file as $value) {
      unlink($value);
    }
  }

  echo <<<EOF
<h2>Mail Sent</h2>
</body>
</html>

EOF;

  exit;
}

// Get the id of the person we are sending the message to
// This is a GET

$id = $_GET['id'];
$subject = $_GET['subject'];

// This page can not be call directly!

if(!isset($id)) {
  print("
<p>You can not use this page directly, it must be referenced from another page!</p>
</body>
</html>
");
  exit;
}

echo "<h1>Send Email</h1>\n";
  
$result = $B->query("select email, fname, lname from wilderness where id='$id'");

$row = mysql_fetch_assoc($result);

$email = $row['email'];
$name = "$row[fname] $row[lname]";
?>

<form id='mailform' name='Email' method='post'
 enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
   <table id='mailformtable' border="1" cellpadding="1" cellspacing="0">
      <tr>
         <td class='left'>From (email address)</td>
         <td>
<?php
print("
<input class='inputtext' id='fromname' type='text'
name='from' tabindex='1'");

if(isset($from)) {
  print(" value='$fromName<$from>'");
}
print(">");
?>
         </td>
         <tr>
            <td class='left'>Subject
            </td>
            <td>
<?php            
echo <<<EOF
<input class='inputtext" id='inputsubject' type='text' name='subject' value='$subject' tabindex='2'>
EOF;
?>
            </td>
         </tr>
         <tr>
            <td class='left'>Message
            </td>

            <td>
               <textarea id='inputmessage' name="message" cols="140" rows="10"
                         id="message" tabindex='3'></textarea>
            </td>
         </tr>
   </table>
   <?php
print("<input type='hidden' name='email' value='$email'>\n");
print("<input type='hidden' name='name' value='$name'>\n");
   ?>
   <input type='hidden' name='mail' value='1'>

   <!-- Attachments -->
   
   <div id='attach' style='border: 1px solid black; width: 20%;'>
      Attachment:<br />
      <input class="clone" name="userfile[]" type="file" /><br />
      <input type='button' value='Another Attachment'/><br/>
   </div>
   <br/>

   <input type='submit' name='submit' value='Send Mail' tabindex='4'>
</form>

<!-- script to focus on first element in form -->
<script type="text/javascript">
document.getElementById('fromname').focus();
</script>

<hr/>

</body>
</html>

<?php
require_once("../blp.i.php");
$B = new Blp;
$headTitle = "Send Multiple Emails";
include("header.i.php");

$referer = $_SERVER['HTTP_REFERER'];

// Don't let people come here from anywhere else than the members
// page! We can change this later to make it only our sites

if(!preg_match("(^http://www.bartonphillips\.com)i", $referer) && ($_REQUEST['mail'] != 1)) {
  if($referer) echo "referer=$referer<br/>";
  
  echo <<<EOL
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
extract(stripSlashesDeep($_POST));

if(!empty($submit)) {
require_once('Mail.php');
require_once('Mail/mime.php');

  // Submited message from this page
  
  $result = $B->query("select * from fairway where id in ($ids)");

  while($row = mysql_fetch_assoc($result)) {
    extract($row);
    $names[$name] = $email; 
  }
    
  $uploads_dir = '/tmp';

  $crlf = "\n";
  $mime = new Mail_mime($crlf);
  $mail =& Mail::factory('mail');

  foreach ($_FILES["userfile"]["error"] as $k => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $tmp_name = $_FILES["userfile"]["tmp_name"][$k];
      $filename = $_FILES["userfile"]["name"][$k];
        //echo "file: $tmp_name, $filename<br>";

      move_uploaded_file($tmp_name, "$uploads_dir/$filename");
      $file[$inx++] = "$uploads_dir/$filename";
    }
  } 

  $hdrs = array(
                'From'    => $from,
                'Subject' => $subject
               );

  $message .= "\n\n--\nGranby Ranch Fairway Cabins Home Owners\n";
             
  $mime->setTXTBody($message);
//  $mime->setHTMLBody($html);

  if(!empty($file)) {
    foreach($file as $v) {
      $mime->addAttachment($v);
    }
  }

//do not ever try to call these lines in reverse order
  $body = $mime->get();
  $hdrs = $mime->headers($hdrs);

  while(list($key, $value) = each($names)) {
    $inx = 0;

    //echo "Mail: $key <$value>, $hdrs, $body<br>";
    
    $mail->send("$key <$value>", $hdrs, $body);

  }
  // Now remove files

  if(!empty($file)) {
    foreach($file as $value) {      
      unlink($value);
    }
  }
  echo "<h1>Mail Sent</h1>\n";
  
} else {
  // Called from members_direcgtory.php

  echo "<h2>Send Multiple Emails</h2>\n";

  $ids = '';
  for($i=0; $i < count($Name); ++$i) {
    $ids .= "$Name[$i],";
  }
  $ids = rtrim($ids, ',');

  $result = $B->query("select * from fairway where id in ($ids)");

  echo <<<EOF
<p>Sent Message to:
<ul>
EOF;

  while($row = mysql_fetch_assoc($result)) {
    extract($row);

    echo <<<EOF
<li>$name</li>
EOF;
}

echo <<<EOF
</ul>
</p>
<hr/>
EOF;

?>

<form id="mailform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
   <table id="mailformtable" border="1" cellpadding="1" cellspacing="0">
      <tr><td class="left">From (email address)</td><td><input class='inputtext'  type="text" name="from"
      /></td></tr>
      <tr><td class="left">Subject:</td><td><input class='inputtext' type="text"
      name="subject"/></td></tr>
      <tr><td class="left">Message:</td><td><textarea name="message" cols="140"
      rows="10"></textarea></td></tr>
   </table>

   <div id='attach' style='border: 1px solid black; width: 20%;'>
      Attachment:<br />
      <input class="clone" name="userfile[]" type="file" /><br />
      <input type='button' value='Another Attachment'/><br/>
   </div>
   <br/>

   <input type='submit' name='submit'
         value="Send Message"/>

<?php   
   echo <<<EOF
<input type='hidden' name='ids' value='$ids'/>
</form>   
<hr/>

EOF;
}
?>

</body>
</html>

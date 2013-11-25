<?php
// Read in the config tile
$FILE = __FILE__;

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

extract(stripSlashesDeep($_POST));

if(!empty($submit)) {
  // Submited message from this page

  $m = "/^\s*$/"; // pattern to match -- line with nothing but white space
  
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

  if($err) {
    echo $PageTitle;
    exit();
  }

  $y = MULTI_QUERY;
  eval("\$x=\"$y\";");

  $result = Query($x);

  $PageTitle = "<h2 align='center'>Your message has been sent to:</h2><p>";

  while($row = mysql_fetch_assoc($result)) {
    extract($row);
    $names["$FName $LName"] = $Email;
    $PageTitle .= "$FName $LName<br/>\n";
  }
  $PageTitle .= "</p>\n";
  
  echo $PageTitle;
    
  $uploads_dir = UPLOAD_DIR;

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

  $message .= MAIL_FOOTER;
             
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
  
} else {
  // Called from members_direcgtory.php

  $ids = '';
  for($i=0; $i < count($Name); ++$i) {
    $ids .= "$Name[$i],";
  }
  $ids = rtrim($ids, ',');

  $y = MULTI_QUERY;
  eval("\$x=\"$y\";");

  $result = Query($x);

  echo <<<EOF
<p>Sent Message to:
<ul>
EOF;

  while($row = mysql_fetch_assoc($result)) {
    extract($row);

    echo <<<EOF
<li>$FName $LName</li>
EOF;
}

  if($MemberId = GetMemberId()) {
    $y = GET_MEMBER;
    eval("\$x=\"$y\";");

    $result = Query($x);

    $row = mysql_fetch_array($result);
    $from = $row['Email'];
    $fromName = "$row[FName] $row[LName]";
  }
  
  echo <<<EOF
</ul>
</p>
<hr/>
<form action="$self" method="post" enctype="multipart/form-data">
   <table>
     <tr>
       <td class='left'>From (email address)</td>
       <td><input class='inputtext' id='fromname' type='text' name='from' tabindex='1'
EOF;

  if(isset($from)) {
    print(" value='$fromName<$from>'");
  }

  echo <<<EOF
        </td>
      </tr>
      <tr>
        <td>Subject:</td><td><input type="text" name="subject"/></td>
      </tr>
      <tr>
        <td>Message:</td><td><textarea name="message" cols="140" rows="10"></textarea></td>
      </tr>

      <tr>
        <th>Attachments:</th>
        <td>
          <div id='attach' style='border: 1px solid black; width: 30%;'>
            <input name="userfile[]" type="file" /><br />
            <input id="attachbutton" type='button' value='Another Attachment'></input><br/>
          </div>
        </td>
      </tr>


      <tr><th colspan='2'><input type='submit' name='submit' value="Send Message"/></th></tr>
   </table>

   <input type='hidden' name='ids' value='$ids'/>
</form>   
<hr/>

EOF;
}

echo $Footer;
?>

</body>
</html>

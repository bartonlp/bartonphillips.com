<?php
// This is a more general version of the one I used for grandchorale

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp;

$DEBUG=0; // set to 1 for debug info or zero for none

$S->nl2br = 1;

if($DEBUG) {
  $echo_command = $echo_response = 1;
} else {
  $echo_command = $echo_response = 0;
}

if(!$S->isBlp()) {
  $errorhdr = <<<EOF
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;

  echo <<<EOF
$errorhdr
<body>
<h1>Sorry This Is Just For Designated Admin Members</h1>
</body>
</html>
EOF;

  exit();
}

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
  case 'POST':
    switch($_POST['page']) {
      case 'verify':
        verify($S, $DEBUG);
        break;

      case 'verifyone':
        verifyone($S, $DEBUG);
        break;
        
      default:
        throw(new Exception("POST invalid page: {$_POST['page']}"));
        break;
    }
    break;
  case 'GET':
    switch($_GET['page']) {
      default:
        start($S, $DEBUG);
        break;
    }
    break;
  default:
    // Main page
    throw(new Exception("Not GET or POST: {$_SERVER['REQUEST_METHOD']}"));
    break;
}

// ********************************************************************************
// Start page
// Ask for a file with contactName, contactEmail

function start($S, $DEBUG) {
  $h->title = "Test Smtp Reciept";  
  $h->banner = <<<EOF
<h1>Test Email Addresses With MX Server</h1>
<hr>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

  echo <<<EOF
$top
<form action="$S->self" method="post" enctype="multipart/form-data">
<p>On your local machine create a CSV file that has the <b>Contact Name</b> and the <b>Contact Email addreess</b> seperated by
a comma. Elements can be encapsolated by double quotes if desired or if the element contains a comma. One Contact/email per line.</p>
<table id="gettemplate">
<tbody>
<tr><th>CSV file on local machine</th><td><input name="tempfile[]" type="file" /></td></tr>
</tbody>
</table>
<input type="hidden" name="page" value="verify" />
<input type="submit" value="Submit"/>
</form>
<hr>
<form action="$S->self" method="post">
<p>Or if you want to validate only one email address just enter it below.</p>
Enter Email address: <input type="text" name="emailaddress"/><br>
<input type="hidden" name="page" value="verifyone"/>
<input type="submit" value="Submit"/>
</form>
$footer
EOF;
}

// ********************************************************************************
// Verify

function verify($S, $DEBUG) {
  //vardump($S, "S");

  $nl2br = $S->nb2br;
  
  $uploads_dir = "/tmp";
  $inx = 0;
  
  foreach ($_FILES["tempfile"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $tmp_name = $_FILES["tempfile"]["tmp_name"][$key];
      $filename = $_FILES["tempfile"]["name"][$key];
      move_uploaded_file($tmp_name, "$uploads_dir/$filename");
      $tempfile[$inx++] = "$uploads_dir/$filename";
    }
  }

  if($inx > 1) {
    echo "inx gt 1: $inx<br>";
    exit();
  }
  
  $S->query("drop table if exists verifyemail");
  $query = <<<EOF
create table verifyemail (
  listId int(11) not null auto_increment,
  contactName varchar(20),
  contactEmail varchar(255) not null,
  teststatus varchar(10),
  primary key(listId)
);
EOF;

  $S->query($query);

  //echo "filel=$tempfile[0]<br>";

  if(($hdl = fopen($tempfile[0], "r")) !== false) {
    while(($ar = fgetcsv($hdl, 0, ",")) !== false) {
      for($i=0; $i < count($ar); ++$i) {
        $ar[$i] = $S->escape($ar[$i]);
      }
      $S->query("insert into verifyemail (contactName, contactEmail, teststatus) values('$ar[0]', '$ar[1]', 'nottested')");
    }
  } else {
    echo "Can't open file $tempfile[0]<br>";
    exit();
  }
  
  $h->title = "Test Smtp Reciept";  
  $h->banner = <<<EOF
<h1>Test Email Addresses With MX Server</h1>
<hr>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

  echo $top;

  $bad = array();
  $dontknow = array();

  $domain = "bartonphillips.com";
  $from_mail = "barton@grandchorale.org";

  $n = $S->query("select listId, contactName, contactEmail from verifyemail where teststatus='nottested'");

  if(!$n) {
    echo "NO RECORDS\n$footer";
    exit();
  }

  while($row = $S->fetchrow('assoc')) {
    extract($row);

    if($contactEmail == "") continue;

    echo "<br><br>**********************<br>\nRecord for $contactName, $contactEmail<br>\n";
  
    // From the email get the mx
    // Extract the host from the email

    if(preg_match("/@(.*)$/", $contactEmail, $m)) {
      $host = $m[1];
    } else {
      echo "Error: $contactEmail<br>\n";
      continue;
    }
  
    if(!getmxrr($host, $mx_records, $mx_weight)) {
      echo "getmxrr failed: $host<br>\n";
      $mx_records = array($host);
      $mx_weight = array(0);
      continue;
    }

    unset($mxs);
  
    // Put the records together in a array we can sort

    for($i=0;$i<count($mx_records);$i++){
      $mxs[$mx_records[$i]] = $mx_weight[$i];
    }

    asort($mxs );
    reset($mxs);

    $to_mail = $contactEmail;

    $ok = 0;
  
    while(list($mx_host, $mx_weight) = each($mxs) ) {
      if($DEBUG) echo "<br>Trying MX Server: $mx_host, Weight: $mx_weight<br><br>\n";

      $smtp_server = $mx_host; 

      $handle = smtp_connect($smtp_server, 25, 30, $echo_command, $echo_response, $nl2br);

      if(!$handle) {
        echo "Can't connect to $smtp_server<br>\n";
        continue;
      }

      if(preg_match("/bartonphillips.com/", $mx_host)) {
        $d = "granbyrotary.org";
      } else {
        $d = $domain;
      }
      //echo "$mx_host, $d<br>";
     
      $ret = smtp_command($handle, "EHLO $d\r\n", $echo_response, $nl2br);

      $ar = explode("\n", $ret);
      if($DEBUG) echo "EHLO: $ret<br>\n";
      foreach($ar as $line) {
        if(!$line) continue;
        if(!preg_match("/^250/", $line)) {
          echo "EHLO Error: $line<br>\n";
          continue;
        }
      }

      $ret =  smtp_command($handle, "MAIL FROM:<$from_mail>\r\n", $echo_response, $nl2br);
      if($DEBUG) echo "MAIL FROM: $ret<br>\n";
      if(!preg_match("/^250/sm", $ret)) {
        if(preg_match("/^550/sm", $ret)) {
          echo "MAIL FROM Error: $ret<br>\n";
          $ok = 2;
        }
        if($ret) 
          continue;
      }

      $ret = smtp_command($handle, "RCPT TO:<$to_mail>\r\n", $echo_response, $nl2br);
      if($DEBUG) echo "REPT TO: $ret<br>\n";
      if(!preg_match("/^250/sm", $ret)) {
        echo "RCPT TO Error: $ret<br>\n";
        if($ret)
          continue;
      }

      // This looks like an OK but does the server just say OK to everything?

      $ret = smtp_command($handle, "RCPT TO:<xyz$to_mail>\r\n", $echo_response, 1);
      if($DEBUG) echo "REPT TO: $ret<br>\n";
      if(preg_match("/^550/sm", $ret)) {
        //echo "RCPT TO Error: $ret<br>\n";
        $ok = 1;
      } else {
        //echo "This Surver says OK to anything<br>\n";
        $ok = 2;
      }
      smtp_command($handle, "QUIT\r\n");
      smtp_close($handle);

      break;
    }
    switch($ok) {
      case 0:
        echo "Contact Email could not be verified for $contactName, $contactEmail<br>\n";
        array_push($bad, "$listId, $contactName, $contactEmail");
        break;
      case 1:
        // OK
        echo "Email OK<br>\n";
        $S->query("update verifyemail set teststatus='ok' where listId='$listId'");
        break;
      case 2:
        echo "Server Says Ok to Anything:  $contactName, $contactEmail<br>\n";
        array_push($dontknow, "$listId, $contactName, $contactEmail");
        break;
    }         
  }

  if(count($bad) > 0) {
    echo "<br>---------------------<br>List Of Bad Emails<br>\n";
    foreach($bad as $line) {
      echo "$line<br>\n";
      if(preg_match("/^(\d+),/", $line, $m)) {
        $id = $m[1];
        $S->query("update verifyemail set teststatus='bad' where listId='$id'");
      } else {
        echo "preg_match did not fine the id?<br>\n";
      }
    }
  }
  if(count($dontknow) > 0) {
    echo "<br>---------------------<br>Can't Say Emails<br>\n";
    foreach($dontknow as $line) { 
      echo "$line<br>\n";
      if(preg_match("/^(\d+),/", $line, $m)) {
        $id = $m[1];
        $S->query("update verifyemail set teststatus='cantsay' where listId='$id'");
      } else {
        echo "preg_match did not fine the id?<br>\n";
      }
    }
  }

  echo <<<EOF
$footer
EOF;
}

// ---------------------------------------------------------------------------

function verifyone($S, $DEBUG) {
  $contactEmail = $_POST['emailaddress'];

  $nl2br = $S->nl2br;
  
  $h->title = "Test Smtp Reciept";  
  $h->banner = <<<EOF
<h1>Test Email Addresses With MX Server</h1>
<hr>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

  echo $top;

  $domain = "bartonphillips.com";
  $from_mail = "barton@grandchorale.org";

  // From the email get the mx
  // Extract the host from the email

  if(preg_match("/@(.*)$/", $contactEmail, $m)) {
    $host = $m[1];
  } else {
    echo "Error: $contactEmail<br>\n$footer";
    exit();
  }
  
  if(!getmxrr($host, $mx_records, $mx_weight)) {
    echo "getmxrr failed: $host<br>\n$footer";
    $mx_records = array($host);
    $mx_weight = array(0);
    exit();
  }

  unset($mxs);
  
  // Put the records together in a array we can sort

  for($i=0; $i<count($mx_records); $i++){
    $mxs[$mx_records[$i]] = $mx_weight[$i];
  }

  asort($mxs );
  reset($mxs);

  //vardump($mxs, "mxs");
  
  $to_mail = $contactEmail;

  $ok = 0;
  
  while(list($mx_host, $mx_weight) = each($mxs) ) {
    if($DEBUG) echo "<br>Trying MX Server: $mx_host, Weight: $mx_weight<br><br>\n";

    $smtp_server = $mx_host; 

    $handle = smtp_connect($smtp_server, 25, 30, $echo_command, $echo_response, $nl2br);

    if(!$handle) {
      echo "Can't connect to $smtp_server<br>\n";
      continue;
    }

    if(preg_match("/bartonphillips.com/", $mx_host)) {
      $d = "granbyrotary.org";
    } else {
      $d = $domain;
    }

    //echo "mx_hostd=$mx_host, d=$d<br>";
     
    $ret = smtp_command($handle, "EHLO $d\r\n", $echo_response, $nl2br);

    $ar = explode("\n", $ret);
    if($DEBUG) echo "EHLO: $ret<br>\n";
    foreach($ar as $line) {
      if(!$line) continue;
      if(!preg_match("/^250/", $line)) {
        echo "EHLO Error: $line<br>\n";
        continue;
      }
    }

    $ret =  smtp_command($handle, "MAIL FROM:<$from_mail>\r\n", $echo_response, $nl2br);
    if($DEBUG) echo "MAIL FROM: $ret<br>\n";

    if(!preg_match("/^250/sm", $ret)) {
      if(preg_match("/^550/sm", $ret)) {
        echo "MAIL FROM Error: $ret<br>\n";
        $ok = 2;
      }
      if($ret) 
        continue;
    }

    $ret = smtp_command($handle, "RCPT TO:<$to_mail>\r\n", $echo_response, $nl2br);
    if($DEBUG) echo "REPT TO: $ret<br>\n";
    if(!preg_match("/^250/sm", $ret)) {
      echo "RCPT TO Error: $ret<br>\n";
      if($ret)
        continue;
    }

    // This looks like an OK but does the server just say OK to everything?

    $ret = smtp_command($handle, "RCPT TO:<xyz$to_mail>\r\n", $echo_response, 1);
    if($DEBUG) echo "REPT TO: $ret<br>\n";
    if(preg_match("/^550/sm", $ret)) {
      //echo "RCPT TO Error: $ret<br>\n";
      $ok = 1;
    } else {
      //echo "This Surver says OK to anything<br>\n";
      $ok = 2;
    }
    smtp_command($handle, "QUIT\r\n");
    smtp_close($handle);
    break;
  }

  switch($ok) {
    case 0:
      echo "Contact Email could not be verified for $contactEmail<br>\n";
      break;
    case 1:
      // OK
      echo "Email address $contactEmail OK<br>\n";
      break;
    case 2:
      echo "Server Says Ok to Anything: $contactEmail<br>\n";
      break;
  }         

  echo $footer;
}

// ********************************************************************************
// Show results

// FUNCTIONS
// do a smtp connect

function smtp_connect($host, $port, $timeout=30, $echo_command=False, $echo_response=False, $nl2br=False) {
  $errno = 0;
  $errstr = 0;
  if($echo_command) {
    if($nl2br) {
      echo nl2br("CONNECTING TO $host\r\n");
    } else {
      echo "CONNECTING TO $host\r\n";
    }
  }

  $handle = @fsockopen($host, $port, $errno, $errstr, $timeout);

//echo "handle=$handle, errno=$errno, errstr=$errstr<br><br>\n";

  if(!$handle || $handle === false || $errstr != '') {
    if($echo_command) {
      if($nl2br) {
        echo nl2br("CONNECTION FAILED\r\n");
      } else {
        echo "CONNECTION FAILED\r\n";
      }
    }
    return false;
  }
  if($echo_command) {
    if($nl2br) {
      echo nl2br("SUCCESS\r\n");
    } else {
      echo "SUCCESS\r\n";
    }
  }

  $response = fread($handle, 1);
  
  $bytes_left = socket_get_status($handle);
  
  if($bytes_left['unread_bytes'] > 0) {
    $response .= fread($handle, $bytes_left["unread_bytes"]);
  }
  if($echo_response) {
    if($nl2br) {
      echo nl2br("Connect Response:\n$response");
    } else {
      echo "Connect Response:\n$response";
    }
  }
  return $handle;
}

// Send a command

function smtp_command($handle, $command, $echo_command=False, $nl2br=False) {
  if($echo_command) {
    if($nl2br) {
      echo nl2br(escapeltgt($command));
    } else {
      echo escapeltgt($command);
    }
  }
  fputs($handle, $command);

  $response = fread($handle, 1);
  $bytes_left = socket_get_status($handle);
  
  if($bytes_left['unread_bytes'] > 0) {
    $response .= fread($handle, $bytes_left["unread_bytes"]);
  }

  if($nl2br) {
    return nl2br($response);
  } else {
    return $response;
  }
}

function smtp_close($handle) {
  //echo "SMTP CLOSED<br>\n";
  fclose($handle);
}

/*
4.2.1 Reply Code Severities and Theory

   The three digits of the reply each have a special significance.  The
   first digit denotes whether the response is good, bad or incomplete.
   An unsophisticated SMTP client, or one that receives an unexpected
   code, will be able to determine its next action (proceed as planned,
   redo, retrench, etc.) by examining this first digit.  An SMTP client
   that wants to know approximately what kind of error occurred (e.g.,
   mail system error, command syntax error) may examine the second
   digit.  The third digit and any supplemental information that may be
   present is reserved for the finest gradation of information.

   There are five values for the first digit of the reply code:

   1yz   Positive Preliminary reply
      The command has been accepted, but the requested action is being
      held in abeyance, pending confirmation of the information in this
      reply.  The SMTP client should send another command specifying
      whether to continue or abort the action.  Note: unextended SMTP
      does not have any commands that allow this type of reply, and so
      does not have continue or abort commands.

   2yz   Positive Completion reply
      The requested action has been successfully completed.  A new
      request may be initiated.

   3yz   Positive Intermediate reply
      The command has been accepted, but the requested action is being
      held in abeyance, pending receipt of further information.  The
      SMTP client should send another command specifying this
      information.  This reply is used in command sequence groups (i.e.,
      in DATA).

   4yz   Transient Negative Completion reply
      The command was not accepted, and the requested action did not
      occur.  However, the error condition is temporary and the action
      may be requested again.  The sender should return to the beginning
      of the command sequence (if any).  It is difficult to assign a
      meaning to "transient" when two different sites (receiver- and
      sender-SMTP agents) must agree on the interpretation.  Each reply
      in this category might have a different time value, but the SMTP
      client is encouraged to try again.  A rule of thumb to determine
      whether a reply fits into the 4yz or the 5yz category (see below)
      is that replies are 4yz if they can be successful if repeated
      without any change in command form or in properties of the sender
      or receiver (that is, the command is repeated identically and the
      receiver does not put up a new implementation.)

   5yz   Permanent Negative Completion reply
      The command was not accepted and the requested action did not
      occur.  The SMTP client is discouraged from repeating the exact
      request (in the same sequence).  Even some "permanent" error
      conditions can be corrected, so the human user may want to direct
      the SMTP client to reinitiate the command sequence by direct
      action at some point in the future (e.g., after the spelling has
      been changed, or the user has altered the account status).

   The second digit encodes responses in specific categories:

   x0z   Syntax: These replies refer to syntax errors, syntactically
      correct commands that do not fit any functional category, and
      unimplemented or superfluous commands.

   x1z   Information:  These are replies to requests for information,
      such as status or help.

   x2z   Connections: These are replies referring to the transmission
      channel.

   x3z   Unspecified.

   x4z   Unspecified.

   x5z   Mail system: These replies indicate the status of the receiver
      mail system vis-a-vis the requested transfer or other mail system
      action.

   The third digit gives a finer gradation of meaning in each category
   specified by the second digit.  The list of replies illustrates this.
   Each reply text is recommended rather than mandatory, and may even
   change according to the command with which it is associated.  On the
   other hand, the reply codes must strictly follow the specifications
   in this section.  Receiver implementations should not invent new
   codes for slightly different situations from the ones described here,
   but rather adapt codes already defined.

   For example, a command such as NOOP, whose successful execution does
   not offer the SMTP client any new information, will return a 250
   reply.  The reply is 502 when the command requests an unimplemented
   non-site-specific action.  A refinement of that is the 504 reply for
   a command that is implemented, but that requests an unimplemented
   parameter.

   The reply text may be longer than a single line; in these cases the
   complete text must be marked so the SMTP client knows when it can
   stop reading the reply.  This requires a special format to indicate a
   multiple line reply.

   The format for multiline replies requires that every line, except the
   last, begin with the reply code, followed immediately by a hyphen,
   "-" (also known as minus), followed by text.  The last line will
   begin with the reply code, followed immediately by <SP>, optionally
   some text, and <CRLF>.  As noted above, servers SHOULD send the <SP>
   if subsequent text is not sent, but clients MUST be prepared for it
   to be omitted.

   For example:

      123-First line
      123-Second line
      123-234 text beginning with numbers
      123 The last line

   In many cases the SMTP client then simply needs to search for a line
   beginning with the reply code followed by <SP> or <CRLF> and ignore
   all preceding lines.  In a few cases, there is important data for the
   client in the reply "text".  The client will be able to identify
   these cases from the current context.

4.2.2 Reply Codes by Function Groups

      500 Syntax error, command unrecognized
         (This may include errors such as command line too long)
      501 Syntax error in parameters or arguments
      502 Command not implemented  (see section 4.2.4)
      503 Bad sequence of commands
      504 Command parameter not implemented

      211 System status, or system help reply
      214 Help message
         (Information on how to use the receiver or the meaning of a
         particular non-standard command; this reply is useful only
         to the human user)

      220 <domain> Service ready
      221 <domain> Service closing transmission channel
      421 <domain> Service not available, closing transmission channel
         (This may be a reply to any command if the service knows it
         must shut down)

      250 Requested mail action okay, completed
      251 User not local; will forward to <forward-path>
         (See section 3.4)
      252 Cannot VRFY user, but will accept message and attempt
          delivery
         (See section 3.5.3)
      450 Requested mail action not taken: mailbox unavailable
         (e.g., mailbox busy)
      550 Requested action not taken: mailbox unavailable
         (e.g., mailbox not found, no access, or command rejected
         for policy reasons)
      451 Requested action aborted: error in processing
      551 User not local; please try <forward-path>
         (See section 3.4)
      452 Requested action not taken: insufficient system storage
      552 Requested mail action aborted: exceeded storage allocation
      553 Requested action not taken: mailbox name not allowed
         (e.g., mailbox syntax incorrect)
      354 Start mail input; end with <CRLF>.<CRLF>
      554 Transaction failed (Or, in the case of a connection-opening
          response, "No SMTP service here")

4.2.3  Reply Codes in Numeric Order

      211 System status, or system help reply
      214 Help message
         (Information on how to use the receiver or the meaning of a
         particular non-standard command; this reply is useful only
         to the human user)
      220 <domain> Service ready
      221 <domain> Service closing transmission channel
      250 Requested mail action okay, completed
      251 User not local; will forward to <forward-path>
         (See section 3.4)
      252 Cannot VRFY user, but will accept message and attempt
         delivery
         (See section 3.5.3)

      354 Start mail input; end with <CRLF>.<CRLF>
      421 <domain> Service not available, closing transmission channel
         (This may be a reply to any command if the service knows it
         must shut down)
      450 Requested mail action not taken: mailbox unavailable
         (e.g., mailbox busy)
      451 Requested action aborted: local error in processing
      452 Requested action not taken: insufficient system storage
      500 Syntax error, command unrecognized
         (This may include errors such as command line too long)
      501 Syntax error in parameters or arguments
      502 Command not implemented (see section 4.2.4)
      503 Bad sequence of commands
      504 Command parameter not implemented
      550 Requested action not taken: mailbox unavailable
         (e.g., mailbox not found, no access, or command rejected
         for policy reasons)
      551 User not local; please try <forward-path>
         (See section 3.4)
      552 Requested mail action aborted: exceeded storage allocation
      553 Requested action not taken: mailbox name not allowed
         (e.g., mailbox syntax incorrect)
      554 Transaction failed  (Or, in the case of a connection-opening
          response, "No SMTP service here")

*/
  
?>


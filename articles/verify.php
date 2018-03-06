<?php
// This shows how to use AUTH PLAIN to log into GMAIL and send a message using SMTP only

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->title = "Test Smtp Reciept";  
$h->banner = <<<EOF
<h1>Log into GMAIL smtp server with AUTH</h1>
<hr>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

echo <<<EOF
$top
<pre>
EOF;

// Connect to the ssl socket
$handle = smtp_connect();

if(!$handle) {
  echo "Can't connect to ssl://smtp.gmail.com\n";
  exit();
}

// Now send the EHLO
$ret = smtp_command($handle, "EHLO bartonphillips.com\r\n");

echo "$ret\n";

$ar = explode("\n", $ret);

foreach($ar as $line) {
  if(!$line) continue;
  if(!preg_match("/^250/", $line)) {
    echo "EHLO Error: $line\n";
    continue;
  }
}

// This is the way the account and password should look
$str = "\000bartonphillips@gmail.com\0007098653Blp";

// Now base64 encode it    
$str = base64_encode($str); //

// Send an AUTH PLAIN with the string    
$ret = smtp_command($handle, "AUTH PLAIN $str\r\n");
echo "$ret\n";

// Who is sending this
$ret =  smtp_command($handle, "MAIL FROM:<barton@applitec.com>\r\n");
echo "$ret\n";

if(!preg_match("/^250/sm", $ret)) {
  if(preg_match("/^550/sm", $ret)) {
    echo "MAIL FROM Error: $ret\n";
  }
  exit();
}

// Now the send to address
$ret = smtp_command($handle, "RCPT TO:<bartonphillips@gmail.com>\r\n");
echo "$ret\n";

if(!preg_match("/^250/sm", $ret)) {
  echo "RCPT TO Error: $ret\n";
}

// Now send DATA
$ret = smtp_command($handle, "DATA\r\n");
echo "$ret\n";

// And then the data followed by a \r\n and a perios
$ret = smtp_command($handle, "FROM: Robert\r\nTO: Bob\r\nSUBJECT: another test\r\nThis is a test\r\n.\r\n");
echo "$ret\n";

// All done so send a QUIT
$ret = smtp_command($handle, "QUIT\r\n");
echo "$ret\n";

echo "</pre><hr>$footer";

// Now close the handle
smtp_close($handle);

exit();

// Connect to the socket

function smtp_connect() {
  $errno = 0;
  $errstr = 0;

  // We want to connect via ssl so add the ssl:// to the front of the smtp address
  $host = 'ssl://smtp.gmail.com';

  $handle = fsockopen($host, 465, $errno, $errstr);

  if(!$handle || $handle === false || $errstr != '') {
    echo "CONNECTION FAILED\nerrstr: $errstr\n";
    return false;
  }

  echo "SUCCESS\n";

  $response = fread($handle, 1);
  
  $bytes_left = socket_get_status($handle);
  
  if($bytes_left['unread_bytes'] > 0) {
    $response .= fread($handle, $bytes_left["unread_bytes"]);
  }
  return $handle;
}

// Send commands

function smtp_command($handle, $command) {
  echo escapeltgt($command);

  fputs($handle, $command);

  $response = fread($handle, 1);
  $bytes_left = socket_get_status($handle);
  
  if($bytes_left['unread_bytes'] > 0) {
    $response .= fread($handle, $bytes_left["unread_bytes"]);
  }

  return $response;
}

// Close the socket

function smtp_close($handle) {
  fclose($handle);
}

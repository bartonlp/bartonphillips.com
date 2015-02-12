#!/usr/bin/php -q
<?php  /*  >php -q server.php  */
// This is a CLI program not a Web program.

$debug   = true;

$cronTime = time();
console("Cron Time Start: $cronTime");

function say($msg="") { echo $msg."\n"; }

function console($msg="") {
  global $debug;
  if($debug) {
    echo "CONSOLE MESSAGE: $msg\n";
  }
}

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
$ipaddress = '69.43.195.42'; //gethostbyname("www.bartonphillips.com");
console("IP address: $ipaddress");
$master  = WebSocket($ipaddress, 12345);
$sockets = array($master);
$users   = array();

while(true) {
  $changed = $sockets;

  //console(print_r($changed, true));

  if(($cnt = socket_select($changed, $write=NULL, $except=NULL, 1)) === false) {
    $err = socket_last_error();
    $errmsg = socket_strerror($err);
    console("socket_select error: $err=$errmsg");
    exit();
  }

  if($cnt) {
    console("socket_select: cnt=$cnt\n");

    foreach($changed as $socket) {
      console("socket: $socket");

      if($socket == $master) {
        console("socket == master");
        $client = socket_accept($master);
        if($client < 0) {
          console("socket_accept() failed");
          continue;
        } else {
          console("Connect: $client");
          connect($client);
        }
      } else {
        if(($bytes = @socket_recv($socket, $buffer, 20000, 0)) === 0) {
          console("socket_read error: ". socket_strerror(socket_last_error()));
          disconnect($socket);
        } else {
          console("Input record size: $bytes");
          $user = getuserbysocket($socket); // function below

          if(!$user->handshake) {
            console("dohandshake user:" . print_r($user, true));
            dohandshake($user, $buffer);
          } else {
            console("process user:" . print_r($user, true));
            process($user, $buffer);
          }
        }
      }
    }
  }
  doCronItem(10, 'something'); // do something every interval seconds. 
}

//---------------------------------------------------------------
// Do something every $interval seconds
// If the something was done returns true else false

function doCronItem($interval, $something) {
  global $cronTime;

  $t = time();

  $next = $cronTime + $interval;
  
  //console("cronTime+int: $next");
  
  if($next > $t) {
    return false;
  } else {
    //console("interval: $interval, cronTime: $cronTime, time: $t");
    $cronTime = $next;
    $something();
    return true;
  }
}

// Callback
function something() {
  global $sockets, $master;

  foreach($sockets as $socket) {
    $user = getuserbysocket($socket);
    if($user == $master || !$user) continue;

    send($user->socket, $user->id);
  }
  console("SOMETHING: " . time());
}

//---------------------------------------------------------------

function process($user, $msg){
  $hexout = '';
  for($i=0; $i<strlen($msg); ++$i) {
     $hexout .= dechex(ord($msg[$i])) . ",";
  }
  console($hexout);

  $msg = hybi10Decode($user->socket, $msg);

  console("action: " . print_r($msg, true));

  $action = $msg['payload'];

  say("< ".$action);
  switch($action){
    case "hello" : send($user->socket, "hello human");
                   break;
    case "hi"    : send($user->socket, "zup human");
                   break;
    case "name"  : send($user->socket, "my name is Multivac, silly I know");
                   break;
    case "age"   : send($user->socket, "I am older than time itself");
                   break;
    case "date"  : send($user->socket, "today is ".date("Y.m.d"));
                   break;
    case "time"  : send($user->socket, "server time is ".date("H:i:s"));
                   break;
    case "thanks": send($user->socket, "you're welcome");
                   break;
    case "bye"   : 
    case "Goodbye": 
      send($user->socket, "See you later");
      close($user->socket, "1000 That's all folks");
      break;
    default      : send($user->socket, $action." not understood");
                   break;
  }
}

function send($client, $msg, $type='text', $mask=false) {
  switch($type) {
    case "text":
      say("> ".$msg);
      break;
    case "close":
      say("close: " . substr($msg, 2));
      break;
  }

  $hexout = 'MSG: ';
  for($i=0; $i<strlen($msg); ++$i) {
    $hexout .= dechex(ord($msg[$i])) . ",";
  }
  console($hexout);

  $msg = hybi10Encode($client, $msg, $type, $mask);
  $hexout = '';
  for($i=0; $i<strlen($msg); ++$i) {
    $hexout .= dechex(ord($msg[$i])) . ",";
  }
  console($hexout);
    
  socket_write($client, $msg, strlen($msg));
}

function WebSocket($address,$port){
  $master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
  socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1)  or die("socket_option() failed");
  if(socket_bind($master, $address, $port) === false) {
    //or die("socket_bind() failed");
    $err = socket_last_error();
    $errmsg = socket_strerror($err);
    echo "socket bind error: $err=$errmsg\n";
    exit();
  }
  socket_listen($master)                                or die("socket_listen() failed");
  echo "Server Started : ".date('Y-m-d H:i:s')."\n";
  echo "Master socket  : ".$master."\n";
  echo "Listening on   : ".$address." port ".$port."\n\n";
  return $master;
}

function connect($socket){
  global $sockets, $users;
  $user = new User();
  $user->id = uniqid();
  $user->socket = $socket;
  array_push($users,$user);
  array_push($sockets,$socket);
  console($socket." CONNECTED!");
}

function disconnect($socket){
  global $sockets,$users;
  $found=null;
  $n=count($users);
  for($i=0;$i<$n;$i++){
    if($users[$i]->socket==$socket){ $found=$i; break; }
  }
  if(!is_null($found)){ array_splice($users,$found,1); }
  $index = array_search($socket,$sockets);
  socket_close($socket);
  console($socket." DISCONNECTED!");
  if($index>=0){ array_splice($sockets,$index,1); }
}

function dohandshake($user, $buffer) {
  console('Requesting handshake...');
  console($buffer);
  
  // Determine which version of the WebSocket protocol the client is using
  if(preg_match("/Sec-WebSocket-Version: (.*)\r\n/ ", $buffer, $match))
    $version = $match[1];
  else 
    return false;

  if($version >= 8) {
    // Extract header variables
    if(preg_match("/GET (.*) HTTP/"   ,$buffer,$match)){ $r=$match[1]; }
    if(preg_match("/Host: (.*)\r\n/"  ,$buffer,$match)){ $h=$match[1]; }
    if(preg_match("/Sec-WebSocket-Origin: (.*)\r\n/",$buffer,$match)){ $o=$match[1]; }
    if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$buffer,$match)){ $k = $match[1]; }

    // Generate our Socket-Accept key based on the IETF specifications
    $accept_key = $k . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    $accept_key = sha1($accept_key, true);
    $accept_key = base64_encode($accept_key);

    $upgrade =  "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: $accept_key\r\n\r\n";

    console($upgrade);
    
    socket_write($user->socket, $upgrade, strlen($upgrade));
    $user->handshake = true;
    return true;
  } else {
    console("Client is trying to use an unsupported WebSocket protocol ({$version})");
    return false;
  }
}

function getheaders($req){
  $r=$h=$o=null;
  if(preg_match("/GET (.*) HTTP/"   ,$req,$match)){ $r=$match[1]; }
  if(preg_match("/Host: (.*)\r\n/"  ,$req,$match)){ $h=$match[1]; }
  if(preg_match("/Origin: (.*)\r\n/",$req,$match)){ $o=$match[1]; }
  if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$req,$match)){ $key=$match[1]; }
  if(preg_match("/\r\n(.*?)\$/",$req,$match)){ $data=$match[1]; }
  $ret = array($r,$h,$o,$key,$data);
  console("getheaders: " . print_r($ret, true));
  return $ret;
}

function getuserbysocket($socket){
  global $users;
  $found=null;
  foreach($users as $user){
    if($user->socket == $socket) {
      $found=$user;
      break;
    }
  }
  return $found;
}

class User{
  var $id;
  var $socket;
  var $handshake;
}

// DECODE

function hybi10Decode($socket, $data) {
  $payloadLength = '';
  $mask = '';
  $unmaskedPayload = '';
  $decodedData = array();

  // estimate frame type:
  $firstByteBinary = sprintf('%08b', ord($data[0]));		
  $secondByteBinary = sprintf('%08b', ord($data[1]));
  $opcode = bindec(substr($firstByteBinary, 4, 4));
  $isMasked = ($secondByteBinary[0] == '1') ? true : false;
  $payloadLength = ord($data[1]) & 127;

  // close connection if unmasked frame is received:
  if($isMasked === false) {
    console("CLOSE 1002");
    close($socket, '1002');
  }

  switch($opcode) {
    // text frame:
    case 1:
      $decodedData['type'] = 'text';				
      break;

    case 2:
      $decodedData['type'] = 'binary';
      break;

      // connection close frame:
    case 8:
      $decodedData['type'] = 'close';
      break;

      // ping frame:
    case 9:
      $decodedData['type'] = 'ping';				
      break;

      // pong frame:
    case 10:
      $decodedData['type'] = 'pong';
      break;

    default:
      // Close connection on unknown opcode:
      console("CLOSE 1003");
      close($socket, '1003');
      break;
  }

  if($payloadLength === 126) {
    $mask = substr($data, 4, 4);
    $payloadOffset = 8;
    $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
  } elseif($payloadLength === 127) {
    $mask = substr($data, 10, 4);
    $payloadOffset = 14;
    $tmp = '';
    for($i = 0; $i < 8; $i++) {
      $tmp .= sprintf('%08b', ord($data[$i+2]));
    }
    $dataLength = bindec($tmp) + $payloadOffset;
    unset($tmp);
  } else {
    $mask = substr($data, 2, 4);	
    $payloadOffset = 6;
    $dataLength = $payloadLength + $payloadOffset;
  }

/**
 * We have to check for large frames here. socket_recv cuts at 1024 bytes
 * so if websocket-frame is > 1024 bytes we have to wait until whole
 * data is transferd. 
 */
  if(strlen($data) < $dataLength) {			
    return false;
  }

  if($isMasked === true) {
    for($i = $payloadOffset; $i < $dataLength; $i++) {
      $j = $i - $payloadOffset;
      if(isset($data[$i])) {
        $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
      }
    }
    $decodedData['payload'] = $unmaskedPayload;
  } else {
    $payloadOffset = $payloadOffset - 4;
    $decodedData['payload'] = substr($data, $payloadOffset);
  }

  return $decodedData;
}

// ENCODE

function hybi10Encode($socket, $payload, $type = 'text', $masked = true) {
  $frameHead = array();
  $frame = '';
  $payloadLength = strlen($payload);

  switch($type)
  {		
    case 'text':
    // first byte indicates FIN, Text-Frame (10000001):
      $frameHead[0] = 129;				
      break;			

    case 'close':
    // first byte indicates FIN, Close Frame(10001000):
      $frameHead[0] = 136;
      break;

    case 'ping':
    // first byte indicates FIN, Ping frame (10001001):
      $frameHead[0] = 137;
      break;

    case 'pong':
    // first byte indicates FIN, Pong frame (10001010):
      $frameHead[0] = 138;
      break;
  }

  // set mask and payload length (using 1, 3 or 9 bytes) 
  if($payloadLength > 65535) {
    $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
    $frameHead[1] = ($masked === true) ? 255 : 127;
    for($i = 0; $i < 8; $i++) {
      $frameHead[$i+2] = bindec($payloadLengthBin[$i]);
    }
    // most significant bit MUST be 0 (close connection if frame too big)
    if($frameHead[2] > 127) {
      close($socket, '1004');
      return false;
    }
  } elseif($payloadLength > 125) {
    $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
    $frameHead[1] = ($masked === true) ? 254 : 126;
    $frameHead[2] = bindec($payloadLengthBin[0]);
    $frameHead[3] = bindec($payloadLengthBin[1]);
  } else {
    $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
  }

  // convert frame-head to string:
  foreach(array_keys($frameHead) as $i) {
  $frameHead[$i] = chr($frameHead[$i]);
  }
  if($masked === true) {
    // generate a random mask:
    $mask = array();
    for($i = 0; $i < 4; $i++) {
      $mask[$i] = chr(rand(0, 255));
    }

    $frameHead = array_merge($frameHead, $mask);			
  }						
  $frame = implode('', $frameHead);

  // append payload to frame:
  $framePayload = array();	
  for($i = 0; $i < $payloadLength; $i++) {		
    $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
  }

  return $frame;
}

function close($socket, $msg) {
  $payload = str_split(sprintf('%016b', $msg), 8);
  $payload[0] = chr(bindec($payload[0]));
  $payload[1] = chr(bindec($payload[1]));
  $payload = implode('', $payload);

  $extra = substr($msg, 4);
  
  switch($msg) {
    case 1000:
      $payload .= 'normal closure';
      break;

    case 1001:
      $payload .= 'going away';
      break;

    case 1002:
      $payload .= 'protocol error';
      break;

    case 1003:
      $payload .= 'unknown data (opcode)';
      break;

    case 1004:
      $payload .= 'frame too large';
      break;		

    case 1007:
      $payload .= 'utf8 expected';
      break;

    case 1008:
      $payload .= 'message violates server policy';
      break;
  }

  if($extra) {
    $payload .= " :: $extra";
  }
  
  send($socket, $payload, 'close', false);
  disconnect($socket);
}

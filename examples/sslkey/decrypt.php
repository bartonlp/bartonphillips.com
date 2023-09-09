<?php
// Define the port number to listen on.

$privatekey = file_get_contents("privatekey");

$port = 8080;

// Create a socket and bind it to the port.
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if($socket === false) {
  echo "can't open socket\n";
  $err = socket_strerror(socket_last_error(null));
  echo "Can't create: $err\n";
  exit();
}

if(socket_bind($socket, '157.245.129.4', $port) === false) {
  $err = socket_strerror(socket_last_error($socket));
  echo "Can't bind: $err\n";
  socket_close($socket);
  exit();
}

// Listen for connections on the socket.

if(socket_listen($socket) === false) {
  $err = socket_strerror(socket_last_error($socket));
  echo "Can't listen: $err\n";
  socket_close($socket);
  exit();
}  

echo "Waiting for client to connect\n";

$client = socket_accept($socket);

if($client === false) {
  echo "can't create client socket\n";
  $err = socket_strerror(socket_last_error($socket));
  echo "Can't accept: $err\n";
  socket_close($socket);
  exit();
} else {
  echo "Port $port open and ready\n";
}

// Accept connections and echo the data back to the client.

while(true) {
  echo "\nWaiting for Data\n";
  
  $data = '';

  // We read chunks but at the end the client sends a single \0.

  $read = [$client];
  $write = $except = [];

  while($in = socket_read($client, 2048)) { // read the encripted base64 data from the client
    //if($in === "\0") { echo "\ngot \\0\n"; break; }
    //echo PHP_EOL, "in: $in", PHP_EOL;
    $data .= $in;
    //echo PHP_EOL, "chunck: $in", PHP_EOL;
    if(socket_select($read, $write, $except, 0) < 1) { echo "\nSelect1 < 1\n"; break; }
  }
  
  if(empty($data)) {
    socket_close($client);
    echo "NO DATA. All Done\n";
    exit();
  }

  echo "Size=" . strlen($data) . ", Client Data: $data\n";
  
  socket_write($client, $data, strlen($data)); // echo the data back to the client

  //echo "Write \\0\n";
  
  //if(socket_write($client, "\0", 1) === false) echo "Error\n";
  
  echo "data written\n";
  
  $data = str_split(base64_decode($data), 256); //every strlen($encrypted) == 256

  $result = '';

  foreach($data as $d){
    //echo "\n$d\n";
    $err = openssl_private_decrypt($d, $decrypted, $privatekey, OPENSSL_PKCS1_OAEP_PADDING);
    if($err === false) {
      echo "\nERROR: ". openssl_error_string(), PHP_EOL;
      exit();
    }
    //echo PHP_EOL, "de: $decrypted", PHP_EOL;
    $result .= $decrypted;
  }

  if($result === false) {
    echo "Close all\n";
    socket_close($client);
    socket_close($socket);
    exit();
  }
  $decoded = "\nThis is decoded: $result\n";

  echo PHP_EOL, "$decoded", PHP_EOL;
  
  socket_write($client, $decoded, strlen($decoded)); // write the decrypted data back to the client
  //if(socket_write($client, "\0", 1) === false) echo "Error\n";
  
//  socket_close($client);
//  socket_close($socket);
//  exit();
}

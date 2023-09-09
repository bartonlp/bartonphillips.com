<?php
// Define the port number to listen on.

$key = $key = openssl_random_pseudo_bytes(20);
$cipher = "aes-128-gcm";
$ivlen = openssl_cipher_iv_length($cipher);
echo "ivlen=$ivlen\n";
$iv = openssl_random_pseudo_bytes($ivlen);
echo "vi=" . bin2hex($iv) . "\n";

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
  $data = socket_read($client, 1024);
  if(!$data) {
    socket_close($client);
    echo "All Done\n";
    exit();
  }
  echo "Data: $data\n";
  socket_write($client, $data);

  if(in_array($cipher, openssl_get_cipher_methods())) {
    echo "method OK\n";
    try {
      $decoded = openssl_decrypt($data, $cipher, $key, $options=0, $iv, $tag);
      if($decoded === false) {
        echo "Can't decode\n";
      } else {
        echo "decoded $decoded\n";
      }
    } catch(Exception $e) {
      $code = $e->getCode();
      $msg = $e->getMessage();
      echo "Exception: $code, $msg\n";
      exit();
    }
  }
  $decoded = "This is decoded: $decoded\n";
  socket_write($client, $decoded);
}

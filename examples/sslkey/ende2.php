<?php
$publickey = file_get_contents("publickey");
$privatekey = file_get_contents("privatekey");

$value = file_get_contents("test.txt");

$data = str_split($value, 214); // max is 214

$result = '';

foreach($data as $d){
  if(openssl_public_encrypt($d, $encrypted, $publickey, OPENSSL_PKCS1_OAEP_PADDING)){
    $result .= $encrypted;
  }
}
echo "encrypted with public key", PHP_EOL;

var_dump($result);

$result = base64_encode($result);

$data = str_split(base64_decode($result), 256); //every strlen($encrypted) == 256

$result = '';

foreach($data as $d){
  if(openssl_private_decrypt($d, $decrypted, $privatekey, OPENSSL_PKCS1_OAEP_PADDING)){
    $result .= $decrypted;
  }
}
echo "decrypted with private key", PHP_EOL;
var_dump($result);

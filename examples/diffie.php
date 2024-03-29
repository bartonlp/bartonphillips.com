<?php
$config = array(
    "digest_alg" => "sha512",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);
   
// Create the private and public key
$res = openssl_pkey_new($config);

// Extract the private key from $res to $privKey
openssl_pkey_export($res, $privKey);

// Extract the public key from $res to $pubKey
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];

$data = 'plaintext data goes here';

// Encrypt the data to $encrypted using the public key
openssl_public_encrypt($data, $encrypted, $pubKey);

// Decrypt the data using the private key and store the results in $decrypted
openssl_private_decrypt($encrypted, $decrypted, $privKey);

echo $decrypted;

//require_once(getenv("SITELOADNAME"));

/*
$modulus_size = 2048;
$generator = 2;

$dh_key_pair = openssl_pkey_new(array(
    "dh_param" => $modulus_size,
    "generator" => $generator
));

$public_key = openssl_pkey_get_public($dh_key_pair);
$private_key = openssl_pkey_get_private($dh_key_pair);

echo "The public key is:\n";
print_r($public_key);
echo "The private key is:\n";
print_r($private_key);

*/
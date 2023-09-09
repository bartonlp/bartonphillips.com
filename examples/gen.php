<?php
require_once(getenv("SITELOADNAME"));
ErrorClass::setNoHtml(true);

use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;

$enc_key = KeyFactory::generateEncryptionKey();

vardumpNoEscape("Key", $enc_key);

KeyFactory::save($enc_key, './encryption.key');
$enc_key = KeyFactory::loadEncryptionKey('./encryption.key');

vardumpNoEscape("Key", $enc_key);

$key_hex = KeyFactory::export($enc_key)->getString();
vardumpNoEscape("hex", $key_hex);
$key = KeyFactory::importEncryptionKey(new HiddenString($key_hex));
vardumpNoEscape("key", $key);

$ciphertext = \ParagonIE\Halite\Symmetric\Crypto::encrypt(
    new HiddenString(
        "Your message here. Any string content will do just fine."
    ),
    $enc_key
  );

echo "\ncypher: $ciphertext\n";

$plaintext = \ParagonIE\Halite\Symmetric\Crypto::decrypt(
    $ciphertext,
    $enc_key
  );

echo "\ntext: " . $plaintext->getString() . "\n";

/************/

$alice_keypair = \ParagonIE\Halite\KeyFactory::generateEncryptionKeyPair();
$alice_secret = $alice_keypair->getSecretKey();
$alice_public = $alice_keypair->getPublicKey();
$send_to_bob = sodium_bin2hex($alice_public->getRawKeyMaterial()); // This is Alice's public key that Bob gets somehow?


$bob_public = new \ParagonIE\Halite\Asymmetric\EncryptionPublicKey(
    new HiddenString(
        sodium_hex2bin($send_to_bob) // This is Bob's key that he has sent to Alice
    )
  );

// This is the encripted text that Alice now sends to Bob.

$send_to_bob = \ParagonIE\Halite\Asymmetric\Crypto::encrypt(
    new HiddenString(
        "Your message here. Any string content will do just fine."
    ),
    $alice_secret,
    $bob_public
  );

// Bob receives the message she sent ($send_to_bob) and decripts it.

$message = \ParagonIE\Halite\Asymmetric\Crypto::decrypt(
    $send_to_bob,
    $alice_secret,
    $bob_public
  );

echo "\nmsg: " . $message->getString() . "\n";

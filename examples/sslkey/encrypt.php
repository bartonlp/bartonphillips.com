<?php
  $plaintext = "I have a secret to tell you.";

  openssl_public_encrypt($plaintext, $encrypted, $keys['public']));

  // Use base64_encode to make contents viewable/sharable
  $message = base64_encode($encrypted);

  echo $message;
  
<?php
// Test the new beacon feature.
// BLP 2014-10-25 -- 
// The navigator.sendBeacon() sends the message as php://input not via $_GET or $_POST!
// The beacon logic is in chrome 39+ only.

$request_body = file_get_contents('php://input');

file_put_contents("debugblp.txt", "got beacon:\n$request_body\n",
                  FILE_APPEND);

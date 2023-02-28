<?php
// Example of how to use 'file_get_contents() to do a POST instead of a GET.
// This uses exampleAjax.php

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$t->test = "test from post_file_get_contents.php";
$t->siteName = "bartonphillips.com";

$json = [test1=>"test one", test2=>"test two"];

$t->json = json_encode($json);

// http_build_query($query_data, ...) takes an object or array. $_site is an array.

$options = ['http' => [
                       'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                       'method'  => 'POST',
                       'content' => http_build_query($t)
                                  ]
                                  ];
                
//vardump("options", $options); // displays:
                // [headerd] => Content-type: application/x-www-form-urlencoded
                // [method] => POST
                // [content] => test=test+from+example2&siteName=bartonlp.com

$context  = stream_context_create($options);
$retvalue = file_get_contents("https://www.bartonphillips.com/examples.js/exampleAjax.php", false, $context);

echo <<<EOF
$top
<h1>Test</h1>
$retvalue
$footer
EOF;

<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site); // $S gives access to my framework.

  exec("ping -c 1 -W 1 xxxbartonlp.com", $out, $ret);
  echo "\n";
  vardump($out);
  echo "ret: $ret<br>";
  
#!/usr/bin/php
<?php
$sug = file_get_contents("/var/www/bartonphillips.com/il-courtyard/suggest.txt");

echo "sug: $sug\n";

if(empty($sug)) {
  $sug = "No Suggestions Tonight";
}

mail("bartonphillips@gmail.com", "Nightly Suggestions", $sug,
     "From: Info\r\nBCC: <bartonphillips@gmail.com>\r\n");
// empty the file.
file_put_contents("/var/www/bartonphillips.com/il-courtyard/suggest.backup", $sug); 
file_put_contents("/var/www/bartonphillips.com/il-courtyard/suggest.txt", '');
echo "nightlyemail DONE\n";

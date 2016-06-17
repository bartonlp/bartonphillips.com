<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.bartonphillips.com");
//curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'My test spider');
$test = curl_exec($ch);
curl_close($ch);
echo "got test";
   

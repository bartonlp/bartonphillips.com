<?php
// Check links in my files.
// Loop over all the php file and check each link to see if it is good or bad. Display bad links.  
  
$_site = require_once(getenv("SITELOADNAME"));

$host =  $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/";

$files = glob("*.php");

use PHPHtmlParser\Dom;
$dom = new Dom;

$cnt = 0;

foreach($files as $file) {
  if($file == "checklinks.php") continue;

  echo "FILE: $file<br>";

  $data = file_get_contents("$host$file");

  $x = $dom->loadStr($data);
  
  $a = $x->find("a");

  foreach($a as $f) {
    $href = $f->getAttribute("href");
    //echo "href1: $href<br>";
    
    if(!is_null($href) && stripos($href, "mailto:") !== 0) {
      if(stripos($href, "http") !== 0) {
        $href = "$host$href";
      }

      //echo "href2: $href<br>";

      if(url_exists($href) === 0) {
        echo "Does Not Exit: $href<br>";
      }
    }
  }
  echo "COUNT: $cnt<br>";
}

function url_exists($url) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url);
  //curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_CONNECT_ONLY, true);
  $ret = curl_exec($ch);
  
  return $ret ? 1 : 0; // return true or false as 1 or 0
}

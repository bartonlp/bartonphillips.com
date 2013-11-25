<?php
// proxy to url This can be used with Ajax so the url is local even though the actual program is
// not.  The query has 'url' with the actual location of the program to run. In addition to 'url'
// there can be any number of additional queries. 

$url = $_GET['url'];  // Get the url
unset($_GET['url']);  // and remove it from $_GET

// Now get any additional queries and make them into a string.

$query = "";

foreach($_GET as $key=>$value) {
  $query .= "$key=$value&";
}

// Remove the trailing ampersand.

$query = trim($query, "&");  

// Use Curl to do the heavy lifting.

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$url?$query"); // This is a simple GET request
curl_setopt($ch, CURLOPT_HEADER, false); // don't want header
//curl_setopt($ch,CURLOPT_USERAGENT, "BLP"); // The url could check to see if the agent is BLP and only allow that agent

curl_exec($ch); // The body is output
curl_close($ch);
?>
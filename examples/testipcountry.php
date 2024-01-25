<?php
$_site = require_once(getenv("SITELOADNAME"));

// Turn an ip address into a long. This is for the country lookup

function Dot2LongIP($IPaddr) {
  if(strpos($IPaddr, ":") === false) {
    if($IPaddr == "") {
      return 0;
    } else {
      $ips = explode(".", "$IPaddr");
      return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
    }
  } else {
    //error_log("IPaddr: $IPaddr");
    $int = inet_pton($IPaddr);
    $bits = 15;
    $ipv6long = 0;

    while($bits >= 0) {
      $bin = sprintf("%08b", (ord($int[$bits])));
      if($ipv6long){
        $ipv6long = $bin . $ipv6long;
      } else {
        $ipv6long = $bin;
      }
      $bits--;
    }
    $ipv6long = gmp_strval(gmp_init($ipv6long, 2), 10);
    //error_log("ipv6long: $ipv6long");
    return $ipv6long;
  }
}

$tkipar = array(); // tracker ip array

$tkipar[] = '45.55.27.116';
$tkipar = array_keys(array_flip($tkipar));

$list = json_encode($tkipar);

$options = array('http' => array(
                                 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                 'method'  => 'POST',
                                 'content' => http_build_query(array('list'=>$list))
                                )
                );

$context  = stream_context_create($options);

// Now this is going to do a POST!

$ipc = file_get_contents("https://www.bartonphillips.com/webstats-ajax.php", false, $context);
vardump("ipc", $ipc);
foreach(json_decode($ipc) as $k=>$v) {
  $ipcountry[$k] = $v;
}

vardump("ipcountry", $ipcountry);
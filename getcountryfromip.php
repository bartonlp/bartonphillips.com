<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

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
    return $ipv6long;
  }
}

// via file_get_contents('webstats.php?list=<iplist>
// Given a list of ip addresses get a list of countries as $ar[$ip] = $name of country.
// FOR THIS APP we only have a single item in the 'list' and this also uses a $_GET not a $_POST as
// does the webstat-ajax.php.

if($list = $_GET['list']) {
  $S = new Database($_site);

  $list = json_decode($list);
  $ar = array();

  foreach($list as $ip) {
    $iplong = Dot2LongIP($ip);
    if(strpos($ip, ":") === false) {
      $table = "ipcountry";
    } else {
      $table = "ipcountry6";
    }
    $sql = "select countryLONG from $S->masterdb.$table ".
            "where '$iplong' between ipFROM and ipTO";

    $S->query($sql);
    
    list($name) = $S->fetchrow('num');

    //error_log("name: $name, iplong: $iplong");
    $ar[$ip] = $name;
  }
  //error_log("ar: ".print_r($ar, true));
  echo json_encode($ar);
  exit();
}

$h->title = "get country from ip";
$h->banner = "<h1>Get Country From IP</h1>";
$h->css = <<<EOF
  <style>
input {
  font-size: 1rem;
  padding: .2rem;
}
button {
  font-size: 1rem;
  padding: .2rem;
  border-radius: .5rem;
}
span {
  color: red;
  font-style: italic;
  font-family: "Times New Roman", Times, serif;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

if($ip = $_POST['ip']) {
  $ip = trim($ip, " \t\n\r\0\x0B_"); // strip the _. Have no idea where that comes from?
  $request = '["'. $ip . '"]';
  $ar = file_get_contents("http://www.bartonlp.com/getcountryfromip.php?list=$request");
  //error_log("AR: $ar");
  $list = json_decode($ar);
  //error_log("LIST: ".print_r($list, true));
  
  $list = $list->$ip;
  //error_log("list: $list");
  // Use ipinfo.io to get the country for the ip
  $cmd = "http://ipinfo.io/$ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));

  $locstr = <<<EOF
<ul class="user-info">
  <li>Hostname: <i class='green'>$loc->hostname</i></li>
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li>GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
</ul>
EOF;
}

echo <<<EOF
$top
<form action='' method='post'>
Enter IP: <input autofocus type='text' name='ip' value='$ip'><br>
<button type='submit'>Submit</button>
</form>
<h2>Country is: <span>$list</span></h2>
$locstr
<hr>
$footer
EOF;

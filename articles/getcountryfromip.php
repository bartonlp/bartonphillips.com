<?php
// BLP 2023-02-25 - use new approach
// BLP 2021-10-03 -- remove the GET and do it all in POST

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

$S->title = "get country from ip";
$S->banner = "<h1>Get Country From IP</h1>";
$S->css = <<<EOF
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
@keyframes spin {
  from {
    transform: rotate(0turn);
  }
  to {
    transform: rotate(1turn);
  }
}
.spinner {
  animation: spin 1000ms;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 50px;
}
EOF;

$S->b_inlineScript =<<<EOF
$("button").on("click", function() {
  $("#name").html("<img class='spinner' src='../test_examples/SvgSpinner.svg'>");
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

// BLP 2021-10-03 -- This now does both the ipcountry and the ipinfo.io lookup

if($ip = $_POST['ip']) {
  //$ip = trim($ip, " \t\n\r\0\x0B_"); // strip the _. Have no idea where that comes from?

  $S = new Database($_site);

  $iplong = Dot2LongIP($ip);
  if(strpos($ip, ":") === false) {
    $table = "ipcountry";
  } else {
    $table = "ipcountry6";
  }
  $sql = "select countryLONG from $S->masterdb.$table ".
         "where '$iplong' between ipFROM and ipTO";

  $S->query($sql);
    
  [$name] = $S->fetchrow('num');
  
  // Use ipinfo.io to get the country for the ip

  $cmd = "http://ipinfo.io/$ip";
  $ch = curl_init($cmd);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $loc = json_decode(curl_exec($ch));

  $locstr = <<<EOF
<h2>Country is: <span>$name</span></h2>
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
<div id="name">
$locstr
</div>
<hr>
$footer
EOF;

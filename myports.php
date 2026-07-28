<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->title = "Port #";
$S->banner = "<h1>SSH Port Numbers</h1>";
$S->css = "table { padding: 2px; } td { padding: 5px; }";

[$top, $footer] = $S->getPageTopBottom();

if($code = $_POST['code']) {
  if($code == "8653") {
    echo <<<EOF
$top
<hr>
<p>This is the DigitalOcean server at ip 208.48.29.57. My MetroNet is 195.252.232.86.</p>
<table border='1'>
<tr><td>bartonphillips.com</td><td>2222<br>This is Ubuntu 24.04 password=bartonl411 and runs with barton2.sh</td></tr>
</table>

<p>These are ports on my MetroNet account with static ip 195.252.232.86. My router is the <i>eero</i>.</p>
<p>For the ssh entries:</p>
<table border='1'>
<tr><td>rpi.bartonphillips.org</td><td>4022
<br>This is Ubuntu 20.04 password=bartonl411 and runs with rpi.sh</td></tr>
<tr><td>spin.bartonphillips.org</td><td>2023<br>
This is Ubuntu 24.04 password=bartonl411 and runs with spin.sh<br>
This is the Windows 11 password=bartonl411 and runs with bartonadmin.sh<br>
This is the Windows 11 password=bartonl411 and runs with bartonadminwsl.sh using 'wsl'</td></tr>
<tr><td>hp2.bartonphillips.org</td><td>4023
<br>This is Ubuntu 22.04 password=bartonl411 and runs with hp2.sh</td></tr>
<tr><td>aspire.bartonphillips.org</td><td>2024<br>
This is Ubuntu 25.10 password=bartonl411 and runs bartonaspire.sh<br>
This is Windows 11 password=bartonl411b and runs bartowin.sh<br>
This is Windows 11 password=Bonwes11$ and runs bonniwin.sh</td></tr>
<tr><td>bartonphillips.org</td><td>2022
<br>This is Ubuntu 24.04 password=bartonl411 and runs directly in HP-envy</td></tr>
</table>
<p>These are <b>Browser</b> ports.</p>
<table border="3">
<tr><td>Browser HP-envy</td><td>no port</td></tr>
<tr><td>Browser Rpi</td><td>8000</td></tr>
<tr><td>Browser Spin</td><td>8080</td></tr>
<tr><td>Browser HP2</td><td>8081</td></tr>
<tr><td>Browser Aspire5</td><td>8084</td></tr>
</table>
<p>On my local net</p>
<table border="3">
<tr><td>HpEnvy</td><td>192.168.4.20</td></tr>
<tr><td>Rpi</td><td>192.168.4.13</td></tr>
<tr><td>Spin</td><td>192.168.4.12</td></tr>
<tr><td>HP2</td><td>192.186.2.11</td></tr>
<tr><td>Aspire5</td><td>192.168.4.29</td></tr>
</table>
<p>These are starting with <b>Ubuntu</b> accounts.</p>
<table border="1">
<tr><td>rpi.sh</td></tr>
<tr><td>spin.sh</td></tr>
<tr><td>hp2.sh.sh</td></tr>
<tr><td>aspine.sh</td></tr>
<tr><td>rpi.sh</td></tr>
</table>
<p>These are starting with <b>Windows 11</b> accounts.</p>
<table border='1'>
<tr><td>bartowin.sh</td><td>password=bartonl411b</td></tr>
<tr><td>bonniwin.sh</td><td>password=bonwes11$</td></tr>
<tr><td>xspin.sh</td><td>password=bartonl411</td></tr>
<tr><td>xaspine.sh</td><td>password=bartonl411b</td></tr>
</table>
<p>These use the <b>mount</b> accounts.</p>
<table border="1">
<tr><td>mountaspine.sh</td><td>Ubuntu, password=bartonl411</td></tr>
<tr><td>mountxaspine.sh</td><td>Windows 11, password=barton411b</td></tr>
<tr><td>mountbarton2.sh</td><td>Ubuntu, password=bartonl411</td></tr>
<tr><td>mountbartowin.sh</td><td>Windows 11, password=bartonl411b</td></tr>
<tr><td>mountbartonadmin.sh</td><td>Windows 11, password=7098653</td></tr>
<tr><td>mountbonniwin.sh</td><td>Windows 11, password=Bonwes11$</td></tr>
<tr><td>mounthp2.sh</td><td>Ubuntu, password=bartonl411</td></tr>
<tr><td>mountspin.sh</td><td>Ubuntu, password=bartonl411b</td></tr>
<tr><td>mountxspin.sh</td><td>Windows 11, password=bartonl411</td></tr>
</table>
<hr>
$footer
EOF;
    exit();
  }
  echo "<h1>NOT AUTHORIZED</h1>";
  exit();
}

echo <<<EOF
$top
<hr>
<form method="post">
Secret Code: <input type="text" name="code" autofocus><br>
<button type="submit">Submit</button>
<form>
<hr>
$footer
EOF;


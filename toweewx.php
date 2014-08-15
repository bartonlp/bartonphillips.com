<?php
   // Weather Station weewx
   // Send to either normal page of smartphone page
/*
CREATE TABLE `detect` (
  `ip` varchar(20) NOT NULL default '',
  `agent` varchar(255) NOT NULL default '',
  `type` text,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ip`,`agent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
/*   
// 401590:vnW1fmKrgZ85sl7NBUMXth46HkTqabye
// Include the WURFL Cloud Client
// You'll need to edit this path
*/

/*
require('/home/bartonlp/includes/WurflCloudClient-PHP-1.0.2-Simple/Client/Client.php');
// Create a configuration object 
$config = new WurflCloud_Client_Config(); 
// Set your WURFL Cloud API Key 
$config->api_key = '401590:vnW1fmKrgZ85sl7NBUMXth46HkTqabye';  
// Create the WURFL Cloud Client 
$client = new WurflCloud_Client_Client($config); 
*/

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

class myDetect extends Mobile_Detect {
  protected $otherOss = array('Linux X11 64' => "Linux x86_64",
                              'Linux X11' => "Linux x86",
                              'Windows XP' => "Windows NT 5\.1",
                              'Windows Vista' => "Windows NT 6\.0",
                              'Windows 7' => "Windows NT 6\.1",
                              'Windows 8' => "Windows NT 6\.2",
                              'Macintosh' => "Macintosh"                                           
                             );

  protected $allOs = array();
  
  public function getOs($userAgent = null) {
    $userAgent = $userAgent ? $userAgent : $this->userAgent;
    $this->allOs = array_merge($this->otherOss, $this->operatingSystems);
    foreach($this->allOs as $k => $v) {
      if(empty($v)){ continue; }
      //echo "$v ($k)<br>";
      if($this->match($v, $userAgent)) {
        //echo "$k: $v<br>";
        if($this->match("windows.*wow64", $userAgent)) {
          $k .= " 64";
        }
        if($k == "Macintosh") {
          echo "$userAgent<br>";
          if(preg_match("/Intel Mac\s+OS\s+X\s+(\d+_\d+_?\d*)/is", $userAgent, $m)) {
            $k .= " Intel Max OS X $m";
          }
        }

        return $k;
      }
    }
    return "OS NOT FOUND";
  }
}

// </endclass

/*
// Detect your device 
$client->detectDevice(); 

$type = '';

// Use the capabilities 
if ($client->getDeviceCapability('is_full_desktop')) {
  $type = "WURFL-desktop: ";
} else {
  if($client->getDeviceCapability('is_tablet')) {
    //echo "This is a tablet";
    $type = 'WURFL-tablet: ';
  } else {
    $type = 'WURFL-mobile: ';
  }
}
*/

$query = "";

$detect = new myDetect();
$os = $detect->getOs();
$type = "$os>>$type";

if($detect->isMobile()) {
  $type .= "myDetect: Mobile";
  if($detect->isTablet()) {
    $type .= ",Tablet";
  }
} else {
  $desktop = true;
  $type .= "Desktop";
}

$db = new Database($dbinfo);

$ip = $_SERVER['REMOTE_ADDR'];
$agent = $db->escape($_SERVER['HTTP_USER_AGENT']);

$query = "select * from detect where ip='$ip' and agent='$agent'";

$n = $db->query($query);

if($n) {
  $row = $db->fetchrow();

  $rtype = $row['type'];
  $extra = "";
  
  if(preg_match("/(^.*?)( ::.*)/", $rtype, $m)) {
    //echo "rtype=$rtype<br>type=$type<br>m={$m[1]}<br>";
    $rtype = $m[1];
    $extra = $m[2];
  } //else echo "not found: $rtype";

  if(($rtype == $type)) {
    $query = "update detect set timestamp=now() where ip='$ip' && agent='$agent'";
  } else {
    $type .= " :: older({$rtype}{$extra})";
    $query = "update detect set type='$type', timestamp=now() where ip='$ip' && agent='$agent'";
  }
  //echo "query: $query<br>";
  //exit();
  $db->query($query);
} else {
  $query = "insert into detect (ip, agent, type) values('$ip', '$agent', '$type')";

  $db->query($query);
}

// Is it Desktop or Mobile?

$d = date("U");
if($desktop) {
  header("location: http://www.bartonphillips.com/weewx/index.html?t=$d");
} else {
  header("location: http://www.bartonphillips.com/weewx/smartphone/index.html?t=$d");
}
?>
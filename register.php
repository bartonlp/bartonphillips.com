<?php
// BLP 2023-02-25 - use new approach
// Register yours name and email address
// This is for bartonphillips.com/index.php
// BLP 2022-07-18 - There are now three places: bartonphillips.net/js/geo.js and the other two
// below. 
// NOTE *** There are only two places where the myip table is inserted or updated,
// bonnieburch.com/addcookie.php and in bartonphillips.com/register.php.

/*
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `finger` varchar(50) NOT NULL,
  `count` int DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`,`finger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3

  The myip table is in $S->masterdb (which should be 'bartonlp') database
  'myIp' will be all of the computers that I have used in the last three days.
  
CREATE TABLE `myip` (
  `myIp` varchar(40) NOT NULL DEFAULT '',
  `count` int DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`myIp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 
*/

$_site = require_once(getenv("SITELOADNAME"));

// AJAX from getFingerprint.js

if($_POST['page'] == 'finger') {
  $S = new Database($_site);

  $visitor = $_POST['visitor'];
  $email = $_POST['email'];
  $name = $_POST['name'];

  if($email == "bartonphillips@gmail.com") {
    // Update the myip tables.

    $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
           "on duplicate key update lasttime=now()";

    $S->query($sql);
    $BLP = "?blp=8653";
  }

  if(!$S->query("select TABLE_NAME from information_schema.tables ".
            "where (table_schema = 'bartonphillips') and (table_name = 'members')")) {
    throw new Exception(__LINE__ .": register.php, members table for database bartonphillips does not exist");
  }

  $S->query("insert into members (name, email, finger, count, created, lasttime) ".
                 "values('$name', '$email', '$visitor', 1, now(), now()) ".
                 "on duplicate key update count=count+1, lasttime=now()");
    
  // Always set the cookie. We use the sql id from the members table.
  // BLP 2021-09-21 -- Add email with ip.

  $options =  array(
                    'expires' => date('U') + 31536000,
                    'path' => '/',
                    'domain' => "." . $S->siteDomain, // leading dot for compatibility or use subdomain
                    'secure' => true,      // or false
                    'httponly' => false,    // or true. If true javascript can't be used.
                    'samesite' => 'Lax'    // None || Lax  || Strict // BLP 2021-12-20 -- changed to Lax
                   );

  if(setcookie('SiteId', "$visitor:$email", $options) === false) {
    echo "Can't set cookie in register.php<br>";
    throw(new Exception("Can't set cookie register.php " . __LINE__));
  }
  echo "$BLP";
  //error_log("$S->ip, visitor: $visitor, email: $email, name: $name");
  exit();
}

$S = new $_site->className($_site);

$S->title = "Register";
$S->css = <<<EOF
input {
  font-size: 1rem;
  padding-left: .5rem;
}
input[type="submit"] {
  border-radius: .5rem;
  background-color: green;
}
EOF;

// The javascript to get the finger etc.

$S->b_script = "<script src='/register.js'></script>";
$S->b_inlineScript = "var blp ='$BLP';";

[$top, $footer] = $S->getPageTopBottom();

// Render Page

echo <<<EOF
$top
<div id="container">
<h1>Register</h1>
<table>
<tbody>
<tr>
<td><span class="lynx">Enter Name </span><input id="name" type="text" name="name" placeholder="Enter Name"></td>
</tr>
<tr>
<td><span class="lynx">Enter Email Address </span><input id="email" type="text" name="email" autofocus required placeholder="Enter Email Address"></td>
</tr>
</tbody>
</table>
<input id="submit" type="submit" value="Submit">
</div>
$footer
EOF;

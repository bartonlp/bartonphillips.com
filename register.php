<?php
// Register yours name and email address
// This is for bartonphillips.com/index.php
// NOTE *** This is the only way to get an ip into the myip table!
/*
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`)
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
ErrorClass::setDevelopment(true);

// If a post from the form

if($_POST) {
  $S = new Database($_site);
  
  $name = $S->escape($_POST['name']);
  $email = $S->escape($_POST['email']);

  // error_log("bartonphillips: name: $name, email: $email");
  
  if($email == "bartonphillips@gmail.com") {
    $name = "Barton Phillips"; // Force name
    //error_log("email: $email");

    // Update the myip tables.
    $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
           "on duplicate key update lasttime=now()";

    $S->query($sql);
  }
  // Do this for everyone.
  // For me the ip is for the last registration and really has no meaning!
  
  $sql = "insert into members (name, email, ip, agent, created, lasttime) ".
         "values('$name', '$email', '$S->ip', '$S->agent', now(), now()) " .
         "on duplicate key update name='$name', email='$email', ip='$S->ip', agent='$S->agent', lasttime=now()";

  $S->query($sql);
  
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

  if(setcookie('SiteId', "$S->ip:$email", $options) === false) {
    echo "Can't set cookie in register.php<br>";
    throw(new Exception("Can't set cookie register.php " . __LINE__));
  }
  header("Location: /");
  exit();
}

$S = new $_site->className($_site);

$h->title = "Register";
$h->css = <<<EOF
  <style>
input {
  font-size: 1rem;
  padding-left: .5rem;
}
input[type="submit"] {
  border-radius: .5rem;
  background-color: green;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Render Page

echo <<<EOF
$top
<h1>Register</h1>
<form method="post">
<table>
<tbody>
<tr>
<td><span class="lynx">Enter Name </span><input type="text" name="name" placeholder="Enter Name"></td>
</tr>
<tr>
<td><span class="lynx">Enter Email Address </span><input type="text" name="email" autofocus required placeholder="Enter Email Address"></td>
</tr>
</tbody>
</table>
<input type="submit" value="Submit">
</form>
$footer
EOF;

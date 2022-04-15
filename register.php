<?php
// Register yours name and email address
// This is for bartonphillips.com/index.php
// NOTE *** There are only two places where the myip table is inserted or updated,
// bonnieburch.com/addcookie.php and in bartonphillips.com/register.php.

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

// If a post from the form

if($_POST) {
  $S = new Database($_site);
  
  $name = $S->escape($_POST['name']);
  $email = $S->escape($_POST['email']);

  // error_log("bartonphillips: name: $name, email: $email");
  
  if($email == "bartonphillips@gmail.com") {
    $name = "Barton Phillips"; // Force name

    if($S->nodb === true) {
      throw new Exception(__LINE__ . ": register.php, nodb is set to true");
    }
    
    $S->query("select count(*) from information_schema.tables ".
              "where (table_schema = '$S->masterdb') and (table_name = 'myip')");

    if(!$S->fetchrow('num')[0]) {
      throw new Exception(__LINE__ .": register.php, myip table does not exist");
    }

    // Update the myip tables.
    $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
           "on duplicate key update lasttime=now()";

    $S->query($sql);
  }
  // Do this for everyone.
  // For me the ip is for the last registration and really has no meaning!
  // The members table is only for database bartonphillips.

  $S->query("select count(*) from information_schema.tables ".
            "where (table_schema = 'bartonphillips') and (table_name = 'members')");

  if(!$S->fetchrow('num')[0]) {
    throw new Exception(__LINE__ .": register.php, members table for database bartonphillips does not exist");
  }
  
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

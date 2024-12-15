<?php
// Register yours name, finger and email address
// This is for bartonphillips.com/index.php
// There are now three places: bartonphillips.net/js/geo.js and the other two below. 
// NOTE *** There are two other places where the myip table is inserted or updated,
// bonnieburch.com/addcookie.php and in bartonphillips.com/register.php.
// NOTE *** This file is a little different, it uses a POST or an Ajax call depending on wheather
// javascript is available (ie. not curl, lync etc. or disabled in the browser). See the if($_POST) bellow.

/*
// BLP 2023-10-13 - added name and ip

CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `finger` varchar(50) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `count` int DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`name`,`email`,`finger`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3

  The myip table is in $S->masterdb (which should be 'barton') database
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

// The POST can happen from the <form> or if javascript is available via script.
// If javascript is available it will replace the <form> logic.

if($_POST['page'] == 'finger') {
  $S = new Database($_site);

  // If we have no \$_POST['visitor'] it probably means javascript is disabled by the user or a browser like lynx, wget, curl etc.

  $visitor = $_POST['visitor'] ?? "NO SCRIPT";
  $email = $_POST['email'];
  $name = $_POST['name'];

  $ip = $_POST['ip']; // If we have an ip that means the <form> sent the post and this is curl like.

  if(!$S->agent) {
    error_log("register.php POST NO-AGENT \$S->agent empty, ip=$ip, email=$email, name=$name");
    header("Location: https://www.bartonphillips.com/register.php?page=complete");
    exit();
  }
  
  // If 'NO SCRIPT' we need to log and goto complete.
  
  if($visitor == "NO SCRIPT") {
    error_log("register.php post: ip=$ip, NO SCRIPT probably javascript disabled or lynx, curl, wget etc., email=$email, name=$name, agent=$S->agent");
    header("Location: https://www.bartonphillips.com/register.php?page=complete");
    exit();
  }

  if($S->isBot($S->agent)) {
    error_log("register.php POST IS-BOT agent=$S->agent, $name, $email, $visitor");
    header("Location: https://www.bartonphillips.com/register.php?page=complete");
  }
  
  if($email == "bartonphillips@gmail.com") {
    // Update the myip tables.

    $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
           "on duplicate key update lasttime=now()";

    $S->sql($sql);
    $BLP = "?blp=8653";
  }

  if(!$S->sql("select TABLE_NAME from information_schema.tables ".
            "where (table_schema = 'bartonphillips') and (table_name = 'members')")) {
    throw new Exception("register.php: members table for database bartonphillips does not exist");
  }

  // BLP 2023-10-13 - Now the key is name, email, finger and ip.
  
  $S->sql("insert into members (ip, name, email, finger, count, created, lasttime) ".
                 "values('$S->ip', '$name', '$email', '$visitor', 1, now(), now()) ".
                 "on duplicate key update count=count+1, lasttime=now()");
    
  $options =  array(
                    'expires' => date('U') + 31536000,
                    'path' => '/',
                    'domain' => "." . $S->siteDomain, // leading dot for compatibility or use subdomain
                    'secure' => true,      // or false
                    'httponly' => false,    // or true. If true javascript can't be used.
                    'samesite' => 'Lax'    // None || Lax  || Strict // BLP 2021-12-20 -- changed to Lax
                   );

  // BLP 2023-09-27 - add name to cookie.
  
  if(setcookie('SiteId', "$name:$visitor:$email", $options) === false) {
    echo "Can't set SiteId cookie in register.php<br>";
    throw(new Exception("register.php: Can't set SiteId cookie"));
  }

  // BLP 2023-09-26 - set the BLP-finger

  if(setcookie('BLP-Finger', "$visitor", $options) === false) {
    echo "Can't set BLP-Finger cookie in register.php<br>";
    throw(new Exception("register.php: Can't set BLP-Finger cookie"));
  }
  
  echo "$BLP";

  exit();
}

$S = new $_site->className($_site);

// BLP 2023-09-27 - Return Page IF we do not have javascript!

if($_GET['page'] == 'complete') {
  $S->title = "Regesteration Complete";
  $S->banner = "<h1>$S->title</h1>";
  [$top, $footer] = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<hr>
<h3>You are either a ROBOT or you do not have JavaScript enables.</h3>
<p>Therefore, you can not register.</p>
<a href='/'>Return to Home Page</a>
<hr>
$footer
EOF;
  exit();
}

// Main Register Page

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
// BLP 2023-09-27 - NOTE if no javascript then we will use the <form> post and $_POST['visitor'] will not be set.
// If we do have javascript we replace the <div id='container'> contents with a new version that
// does not have a <form>.

$S->b_inlineScript =<<<EOF
'use strict';

const ajaxFile = "register.php";

console.log(ajaxFile);
console.log("lastId: "+lastId);
//debugger; // BLP 2021-12-29 -- Force a breakpoint here

// This is the version that replaces the contents of <div id='container'>

let src = `
<hr>
<h1>Register</h1>
<p id="msg" style="color: red"></p>
<table>
<tbody>
<tr>
<td>Enter Name</td><td><input id="name" type="text" name="name" placeholder="Enter Name"></td>
</tr>
<tr>
<td>Enter Email Address</td><td><input id="email" type="text" name="email" autofocus placeholder="Enter Email Address"></td>
</tr>
</tbody>
</table>
<input id="submit" type="submit" value="Submit">
<input type="hidden" name="page" value="finger">
<hr>
`;

// Replace the 'container' with the html above. We have JavaScript

$("#container").html(src);

$("#submit").on("click", function(e) {
  //debugger;
  let email = $("#email").val();
  let name = $("#name").val();
  let msg = '';

  if(!name) { msg = "Name required<br>"; }
  if(!email) { msg += "Email required"; }

  if(msg) {
    $("#msg").html(msg);
    e.stopPropagation();
    return;
  }
  // BLP 2023-08-19 - fpPromise instantiated in goe.js

  fpPromise
  .then(fp => fp.get())
  .then(result => {
    // This is the visitor identifier:
    const visitorId = result.visitorId;

    console.log("visitor: " + visitorId);

    $.ajax({
      url: ajaxFile,
      data: { page: 'finger', visitor: visitorId, email: email, name: name },
      type: 'post',
      success: function(data) {
        console.log("return: " + data);
        $("#container").html("<hr><h1>Registration Complete</h1><a href='/" + data + "'>Return to Home Page</a><hr>");
      },
      error: function(err) {
        console.log(err);
      }
    });
  });
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

// Render Page
// Note that if we have javascript the <div id='container'> will all be replaced.
// The container only has the <form> tag if NO SCRIPT.

echo <<<EOF
$top
<div id="container">
<hr>
<!--
This container us usually replaced by the text in JavaScript.
If javascript is not available, either because it is turned off in the browser or the client is curl, lynx etc.,
then we will use this <form ...>. NOTE there is no 'visitor' in the \$_POST['visitor'].
Therfore, 'finger' in the 'members' table is marked as 'NO SCRIPT'.
-->
<form action='register.php' method='post'>
<h1>Register</h1>
<table>
<tbody>
<tr>
<td>Enter Name</td><td><input id="name" type="text" name="name" required placeholder="Enter Name"></td>
</tr>
<tr>
<td>Enter Email Address</td><td><input id="email" type="text" name="email" autofocus required placeholder="Enter Email Address"></td>
</tr>
</tbody>
</table>
<input id="submit" type="submit" value="Submit">
<input type="hidden" name="page" value="finger">
<input type="hidden" name="ip" value="$S->ip">
</form>
<hr>
</div>
$footer
EOF;

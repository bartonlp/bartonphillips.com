<?php
// Register yours name, finger and email address
// This is for bartonphillips.com/index.php
// NOTE *** There are three places where the myip table is inserted or updated,
// bonnieburch.com/addcookie.php and in bartonphillips.com/register.php and
// bartonphillips.com/index.i.php.
//
// NOTE *** This file is a little different, it uses a POST or an Ajax call depending on wheather
// javascript is available (ie. not curl, lync etc. or disabled in the browser). See the if($_POST) bellow.

/*
CREATE TABLE `tracker` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `botAs` varchar(100) DEFAULT NULL,
  `site` varchar(25) DEFAULT NULL,
  `page` varchar(255) NOT NULL DEFAULT '',
  `finger` varchar(50) DEFAULT NULL,
  `nogeo` tinyint(1) DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `count` int DEFAULT 1,
  `agent` text,
  `referer` varchar(255) DEFAULT '',
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `difftime` varchar(20) DEFAULT NULL,
  `isJavaScript` int DEFAULT '0',
  `error` varchar(256) DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site` (`site`),
  KEY `ip` (`ip`),
  KEY `lasttime` (`lasttime`),
  KEY `starttime` (`starttime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

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

CREATE TABLE `myip` (
  `myIp` varchar(40) NOT NULL DEFAULT '',
  `count` int DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`myIp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 
*/

$_site = require_once getenv("SITELOADNAME"); // Get $_site. The post will use Database and the main render will use SiteClass.

// *****************************************************************************
// NOTE: the POST can happen from the <form> or, if javascript is available, via the JavaScript in
// this page. If javascript is available the JavaScript will replace the <form> logic.
// If the <form> is how we got here then $_POST['ip'] will be set and $_POST['visitor'] is not set.

if($_POST['page'] == 'finger') {
  $S = new Database($_site);

  // If we have no $_POST['visitor'] it probably means javascript is disabled by the user or a browser like lynx, wget, curl etc.

  $visitor = $_POST['visitor'] ?? "NO SCRIPT";
  $email = $_POST['email'];
  $name = $_POST['name'];

  $ip = $_POST['ip']; // If we have an ip that means the <form> sent the post and this is curl like.

  if(!$S->agent) {
    error_log("register.php POST NO-AGENT \$S->agent empty: ip=$ip, email=$email, name=$name, line=". __LINE__);

    header("Location: https://www.bartonphillips.com/register.php?page=complete");
    exit();
  }
  
  // If 'NO SCRIPT' we need to log and goto complete.
  
  if($visitor == "NO SCRIPT") {
    error_log("register.php post NO_SCRIPT: ip=$ip, probably javascript disabled or lynx, curl, wget etc., ".
              "email=$email, name=$name, agent=$S->agent, line=". __LINE__);

    header("Location: https://www.bartonphillips.com/register.php?page=complete");
    exit();
  }

  if($S->isBot($S->agent)) {
    error_log("register.php POST IS-BOT: agent=$S->agent, name=$name, email=$email, visitor=$visitor, line=". __LINE__);

    header("Location: https://www.bartonphillips.com/register.php?page=complete");
    exit();
  }

  // At this point I know that this was an AJAX call not a POST, so $BLP will be returned via an
  // echo.
  
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

  // The key is name, email, finger and ip.
  
  $S->sql("insert into members (ip, name, email, finger, count, created, lasttime) ".
          "values('$S->ip', '$name', '$email', '$visitor', 1, now(), now()) ".
          "on duplicate key update count=count+1, lasttime=now()");

  // Setup $options for setcookie().
  
  $options =  array(
                    'expires' => date('U') + 31536000,
                    'path' => '/',
                    'domain' => "." . $S->siteDomain, // leading dot for compatibility or use subdomain
                    'secure' => true,      // or false
                    'httponly' => false,    // or true. If true javascript can't be used.
                    'samesite' => 'Lax'    // None || Lax  || Strict // BLP 2021-12-20 -- changed to Lax
                   );

  if(setcookie('SiteId', "$name:$visitor:$email", $options) === false) {
    echo "Can't set SiteId cookie in register.php<br>";
    throw(new Exception("register.php: Can't set SiteId cookie"));
  }

  // Set the BLP-finger cookie. Same $options as above.

  if(setcookie('BLP-Finger', "$visitor", $options) === false) {
    echo "Can't set BLP-Finger cookie in register.php<br>";
    throw(new Exception("register.php: Can't set BLP-Finger cookie"));
  }
  
  echo "$BLP"; // Ajax return value.

  exit();
}

$S = new SiteClass($_site); // Use SiteClass for the renders.

// Return this page if we do not have JavaScript!

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

// The JavaScript to get the finger etc.
// NOTE if no JavaScript then we will use the <form> post and $_POST['visitor'] will not be set,
// but $ip will be set.
// If we do have JavaScript we replace the <div id='container'> contents with a new version that
// does not have a <form>.

$S->b_inlineScript =<<<EOF
'use strict';

const ajaxFile = "register.php";

console.log(ajaxFile);
console.log("lastId: "+lastId);

//debugger; // Force a breakpoint here

// Replacement 'src' for the <form> logic in 'container'.

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

// Replace the 'container' with the html above. We have JavaScript.

$("#container").html(src);

$("#submit").on("click", function(e) {
  //debugger;
  let email = $("#email").val();
  let name = $("#name").val();
  let msg = '';

  if(!name) { msg = "Name required<br>"; }
  if(!email) { msg += "Email required"; }

  if(msg) {
    $("#msg").html(msg);  // Post error
    e.stopPropagation();
    return;
  }

  // NOTE: fpPromise is instantiated in goe.js

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

        // Now change #container. We return to our home page. 'data' should be blp=8653.

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
// Note that if we have JavaScript the <div id='container'> will all be replaced.
// The container only has the <form> tag if NO SCRIPT.

echo <<<EOF
$top
<!--
The contents of this container are usually replaced by the text from JavaScript.
If JavaScript is not available, either because it is turned off in the browser or the client is curl, lynx etc.,
then we will use this <form ...>. NOTE there is no 'visitor' in the \$_POST['visitor'].
Therfore, 'finger' in the 'members' table is marked as 'NO SCRIPT'.
-->
<div id="container">
<hr>
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

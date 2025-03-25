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

CREATE TABLE `bots` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `agent` text NOT NULL,
  `count` int DEFAULT NULL,
  `robots` int DEFAULT '0',
  `site` varchar(255) DEFAULT NULL,
  `creation_time` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`,`agent`(254))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `bots2` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `agent` text NOT NULL,
  `page` text,
  `date` date NOT NULL,
  `site` varchar(50) NOT NULL DEFAULT '',
  `which` int NOT NULL DEFAULT '0',
  `count` int DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`,`agent`(254),`date`,`site`,`which`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

// Small helper function for $_POST['page'] == 'finger'.
// Update the tracker table and the two bots tables bots and bots2.

function updateTables($id, $ip, $agent, $S) {
  // I can using a single MySql statment.
  // This is quite a mouth full but it works.
  // UPDATE $S->masterdb.tracker SET error =
  //  CASE 
  //   WHEN error IS NULL 
  //    THEN 'register' 
  //   WHEN error NOT LIKE '%register%' 
  //    THEN CONCAT('register', error) 
  //   ELSE  error  
  //  END 
  // WHERE id = $id;
  
  $S->sql("update $S->masterdb.tracker set isJavaScript=isJavaScript | " . TRACKER_BOT .
          ", error=case when error is null then 'register' ".
          "when error not like '%register%' then concat(error, ',register') ".
          "else error end " .
          "where id=$id");
  
  // I should be able to insert/update bots and bot2

  $S->sql("insert into $S->masterdb.bots (ip, agent, count, robots, site, creation_time, lasttime) ".
          "values('$ip', '$agent', 1, ". BOTS_SITECLASS . ", '$S->siteName', now(), now()) ".
          "on duplicate key update robots=robots | " . BOTS_SITECLASS . ", count=count+1, lasttime=now()");

  $S->sql("insert into $S->masterdb.bots2 (ip, agent, page, date, site, which, count, lasttime) ".
          "values('$ip', '$agent', '$S->self', date(now()), '$S->siteName', " . BOTS_SITECLASS . ", 1, lasttime=now()) ".
          "on duplicate key update count=count+1, lasttime=now()");
}

// *****************************************************************************
// NOTE: the POST can happen from the <form> or, if javascript is available, via the JavaScript in
// this page. If javascript is available the JavaScript will replace the <form> logic.
// If the <form> is how we got here then $_POST['ip'] will be set and $_POST['visitor'] is not set.

if($_POST['page'] == 'finger') {
  $S = new Database($_site);

  $visitor = $_POST['visitor']; // If NULL then no JavaScript.
  $email = $_POST['email'];
  $name = $_POST['name'];
  $myid = $_POST['myid'];
  $myip = $_POST['myip'];
  
  // If 'NO SCRIPT' we need to log and goto complete.
  
  if(!$visitor || !$S->agent || ($bot = $S->isBot($S->agent)) === true) {
    $bot = $bot ? "true" : "false";
    
    error_log("register.php post: myid=$myid, myip=$myip, agent=$S->agent, isBot=$bot ".
              "email=$email, name=$name, line=". __LINE__);

    updateTables($myid, $myip, $agent, $S);
    
    header("Location: https://www.bartonphillips.com/register1.php?page=complete");
    exit();
  }

  // At this point I know that this was an AJAX call not a POST, so $BLP will be returned via an
  // echo.
  
  if($email == "bartonphillips@gmail.com") {
    // Update the myip tables.

    "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
    "on duplicate key update lasttime=now()";

    $S->sql("insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
            "on duplicate key update lasttime=now()");
    
    $BLP = "blp=8653";
  }

  // The key is name, email, finger and ip.

  try { // Make sure this table exists in the bartonphillips database.
    $S->sql("insert into bartonphillips.members (ip, name, email, finger, count, created, lasttime) ".
            "values('$S->ip', '$name', '$email', '$visitor', 1, now(), now()) ".
            "on duplicate key update count=count+1, lasttime=now()");
  } catch(Exception $e) {
    // It could be no database.
    
    $errno = $e->getCode();
    $errmsg = $e->getMessage();
    throw new Exception("register.php: $errno, $errmsg");
  }

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
$S->preheadcomment = "<!-- Register1.php -->";

// Return this page if we do not have JavaScript!

if($_GET['page'] == 'complete') {
  $S->title = "Regesteration Complete";
  $S->banner = "<h1>$S->title</h1>";
  [$top, $bottom] = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<hr>
<h3>You are either a ROBOT or you do not have JavaScript enables.</h3>
<p>Therefore, you can not register.</p>
<a href='/'>Return to Home Page</a>
<hr>
$bottom
EOF;
  exit();
}

$myid = $_GET['myid'];
$myip = $_GET['myip'];

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

const ajaxFile = "register1.php";

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
<input id="myid" type="hidden" name="myid" value="$myid">
<input id="myip" type="hidden" name="myip" value="$myip">
<input id="submit" type="submit" value="Submit">
<hr>
`;

// Replace the 'container' with the html above. We have JavaScript.

$("#container").html(src);

$("#submit").on("click", function(e) {
  //debugger;
  const email = $("#email").val();
  const name = $("#name").val();
  const myid = $("#myid").val();
  const myip = $("#myip").val();
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
    //const visitorId = null;
    console.log("visitor: " + visitorId);

    $.ajax({
      url: ajaxFile,
      data: { page: 'finger', visitor: visitorId, email: email, name: name, myid: myid, myip: myip },
      type: 'post',
      success: function(data) {
        console.log("return: " + data);

        if(data === 'blp-8653') {
          $("#container").html("<hr><h1>Registration Complete</h1><a href='/?" + data + "'>Return to Home Page</a><hr>");
        } else {
          document.documentElement.innerHTML = data;
        }
      },
      error: function(err) {
        console.log(err);
      }
    });
  });
});
EOF;

[$top, $bottom] = $S->getPageTopBottom();

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
<form action='register1.php' method='post'>
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
<input type="hidden" name="myid" value="$myid">
<input type="hidden" name="myip" value="$myip">
</form>
<hr>
</div>
$bottom
EOF;

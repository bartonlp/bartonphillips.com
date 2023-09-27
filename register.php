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

// The POST can happen from the <form> or if javascript is available via script. The javascript
// will replace the <form> logic.

if($_POST['page'] == 'finger') {
  $S = new Database($_site);

  // BLP 2023-09-19 - if we have no $visitor it probably means javascript is disabled by the user.
  // Or a browser like lynx, wget, curl etc.

  $visitor = $_POST['visitor'] ?? "NO SCRIPT";
  
  $email = $_POST['email'];
  $name = $_POST['name'];

  if($S->isBot($S->agent)) header("location: https://www.bartonphillips.com");
  
  if($email == "bartonphillips@gmail.com") {
    // Update the myip tables.

    $sql = "insert into $S->masterdb.myip (myIp, createtime, lasttime) values('$S->ip', now(), now()) " .
           "on duplicate key update lasttime=now()";

    $S->query($sql);
    $BLP = "?blp=8653";
  }

  if(!$S->query("select TABLE_NAME from information_schema.tables ".
            "where (table_schema = 'bartonphillips') and (table_name = 'members')")) {
    throw new Exception("register.php: members table for database bartonphillips does not exist");
  }

  $S->query("insert into members (name, email, finger, count, created, lasttime) ".
                 "values('$name', '$email', '$visitor', 1, now(), now()) ".
                 "on duplicate key update count=count+1, lasttime=now()");
    
  $options =  array(
                    'expires' => date('U') + 31536000,
                    'path' => '/',
                    'domain' => "." . $S->siteDomain, // leading dot for compatibility or use subdomain
                    'secure' => true,      // or false
                    'httponly' => false,    // or true. If true javascript can't be used.
                    'samesite' => 'Lax'    // None || Lax  || Strict // BLP 2021-12-20 -- changed to Lax
                   );

  if(setcookie('SiteId', "$visitor:$email", $options) === false) {
    echo "Can't set SiteId cookie in register.php<br>";
    throw(new Exception("register.php: Can't set SiteId cookie"));
  }

  // BLP 2023-09-26 - set the BLP-finger

  if(setcookie('BLP-Finger', "$visitor", $options) === false) {
    echo "Can't set BLP-Finger cookie in register.php<br>";
    throw(new Exception("register.php: Can't set BLP-Finger cookie"));
  }
  
  echo "$BLP";

  if($visitor == "NO SCRIPT") {
    error_log("register.php post: NO SCRIPT probably javascript disabled or lynx, curl, wget etc., email=$email, name=$name");
    header("Location: https://www.bartonphillips.com/register.php?page=complete");
  }
  exit();
}

$S = new $_site->className($_site);

// Return Page

if($_GET['page'] == 'complete') {
  $S->title = "Regesteration Complete";
  $S->banner = "<h1>$S->title</h1>";
  [$top, $footer] = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<hr>
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
// NOTE if no javascript then we will use the <form> post and $_POST['visitor'] will not be set.
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
// The container only has a <form> if NO SCRIPT.

echo <<<EOF
$top
<div id="container">
<hr>
<form action='register.php' method='post'> <!-- If no javascript we will use this post -->
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
</form>
<hr>
</div>
$footer
EOF;

<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new PokerClub;

$footer = $S->getFooter("<hr>");

$h->title = "Poker Login";

$h->extra = <<<EOF
  <script type="text/javascript">
<!--
jQuery.noConflict();

jQuery(document).ready(function($) {
  $("#pokerflush").animate({ 
opacity: 1.0,
left: 100
  }, {duration: 5000 });
  
});
//-->  
  </script>


  <!-- Inline CSS if any -->
  <style type='text/css'>
body {
        background-color: lightblue;
}
#header {
        margin: 0 auto;
        width: 20%;
        height: 210px;
}
.extra {
        color: white;
        background-color: blue;
}
#memberstbl {
        border: 1px solid black;
        background-color: white;
}
#memberstbl th, #memberstbl td {
        border: 1px solid black;
}
#popup td, #popup th {
        border: 1px solid black;
}
#todayGuests, #todayGuests * {
        background-color: white;
        border: 1px solid black;
}
#todayGuests * {
        padding: 5px;
}
#wrapper {
}
#left {
        float: left;        
}
#right {
        float: right; margin-right: 50px;
}
  </style>
EOF;
            
$top = $S->getPageTop($h);

// Get the POST variables 

extract($_POST);

// Initially $password is NOT set! It is only set after the initial
// call to this page.
// Check to see if the password and username match what is in the
// database

if(isset($email)) {
  // This logic processed the information form the initial call to
  // this page.

  $n = $S->query("select id, username from pokermembers where Email='$email'");
    
  // Check to see if we got a result
  if(!$n) {
    echo <<<EOF
$top
<h1>Email Address not found</h1>
<p>Check the spelling of your <b>Email Address</b>, which is case sensitive. Make sure
your caps lock is not on. <a href='$S->self'>Try again</a>?</p>
<p>Or return to our <a href='index.php'>home page</a></p>
$footer
EOF;
    exit();
  }

  $row = $S->fetchrow('assoc');

  $id = $row['id'];
  $S->SetIdCookie($id);

  echo <<<EOF
$top
<h1>Set Up Cookie</h1>
<p>You are logged in. We have set a login cookie in your browser so you will not have to login again.</p>
<p>Please take a moment to update your <a href='updateprofile.php'> user profile</a>. </p>
<p><a href='index.php'>Return to Home Page</a></p>
$footer
EOF;
    exit;
} else {
  // This is the Initial page!

  // is the id set? if not then NO cookie yet

  if($S->id == 0) {
    // No Id Yet. Show the 'get email address dialog

    echo <<<EOF
$top
<h1>Set Up Cookie</h1>
<p>Please enter your <b>Email address</b>.</p>

<form action='$S->self' method='post'>
Enter Email Address: <input type='text' name='email' />
<input type='submit' name='submit'>
</form>
$footer
EOF;
   exit;
  } else {
   echo <<<EOF
$top
<p>You are already logged in. Thanks</p>
$footer
EOF;
  }
}
?>

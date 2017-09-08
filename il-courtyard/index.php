<?php
// il-courtyard
// Get Independent Living Suggestions

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

// Save the suggestion

if($_POST['page'] == 'save') {
  $date = $_POST['date']; // Y-m-d
  $date2 = $_POST['date2']; // full date
  $name = $_POST['name'];
  $apt = $_POST['apt'];
  $sug = $_POST['sug'];
  $email = $_POST['email'];
  
  $msg = "$date\n$name -- $apt\n$sug";

  //file_put_contents("/var/www/bartonphillips.com/il-courtyard/suggest.txt",
  //                  "*******\n$date\n$name -- $apt\n$sug\n$email\n", FILE_APPEND);

  $sql = "insert into courtyard (date, date2, name, apt, comment, email) ".
         "values('$date', '$date2', '$name', '$apt', '$sug', '$email')";

  try {
    $S->query($sql);
  } catch(Exception $e) {
    if($e->getCode() == 1146) {
      //error_log("Create it");
      $S->query("create table courtyard (date date, date2 varchar(50), name varchar(100), ".
                "apt varchar(50), comment text, email varchar(255)) engine=MyISAM");
    }
    $S->query($sql);
  }
  
/*  mail("2526706424@vtext.com", "SUGGESTION", $msg,
       "From: <$email>\r\n",
       "-f$email");
*/
  echo "Done\n$msg\n$email";
  exit();
}

// Login if no cookie exists

if($_POST['page'] == 'login') {
  vardump("login", $_POST);
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $apt = $_POST['apt'];
  $email = $_POST['email'];                  
                  
  $S->setSiteCookie('login', "$fname,$lname,$apt,$email", time()+60*60*24*365); 
  exit();
}

$h->css =<<<EOF
  <style>
#courtyard {
  width: 450px;
}
table {
  width: 100%;
}
table tr td:first-child {
  width: 6rem;
}
table input {
  font-size: 1rem;
  border-radius: .5rem;
}
table textarea {
  width: 100%;
  height: 10rem;
  font-size: 1rem;
}
#reset {
  cursor: pointer;
  background-color: pink;
  font-size: 1rem;
  border-radius: .5rem;
  color: white;
  margin-top: 1rem;
}
.hide { /* hide the date */
  display: none; 
}
@media (max-width: 500px) {
  #courtyard {
    width: 100%;
  }
}
  </style>
EOF;

// Check the login cookie to see if we are logged in.
// If NOT then login and save the cookie via the above.

if(!$_COOKIE['login'] || $_GET['page'] == 'reset') {
  $h->title = "Login";
  $h->banner = "<h1>You Must First Login</h1>";
  $h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  $("#submit").click(function(data) {
    var fname = $("table tr:first-child input").val();
    var lname = $("table tr:nth-child(2) input").val();
    var apt = $("table tr:nth-child(3) input").val();
    var email = $("table tr:nth-child(4) input").val();
    $.ajax({
      url: 'index.php',
      data: {page: 'login', fname: fname, lname: lname, apt: apt, email: email},
      type: 'post',
      success: function(data) {
        console.log(data);
        location = 'index.php';
      },
      error: function(err) {
        console.log(err);
      }
    });
    return false;
  });
});
  </script>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h);

  echo <<<EOF
$top
<p>Please Login with your First and Last Name, your appartment number and your email address.</p>
<form>
<table>
<tr>
<td>First Name</td><td><input autofocus type='text' required></td>
</tr>
<tr>
<td>Last Name</td><td><input type='text' required></td>
</tr>
<tr>
<td>Appartment</td><td><input type='text' required></td>
</tr>
<tr>
<td>Email Address</td><td><input type='text' required></td>
</tr>
<tr>
<td colspan='2'><input type='submit' value='Submit' id='submit'></td>
</tr>
</table>
</form>
$footer
EOF;

  exit();
}

// This is the main thread. Get the suggestion

$h->title = "il-courtyard";
$h->banner = "<h1>Independent Living Group</h1>";

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  $("#submit").click(function(e) {
    var date2 = $("table tr:first-child td:nth-child(2) span:first-child").text(); // full date
    var date = $("table tr:first-child td:nth-child(2) .hide").text(); // Y-m-d
    var name = $("#name").val();
    var apt = $("#apt").val();
    var sug = $("form textarea").val();
    var email = $("#email").val();

    if(!sug) {
      $("tr:nth-child(2) td:first-child").html("Your Must Enter a Suggestion:").css({color: 'red'});
      return false;
    }

    $.ajax({
      url: "index.php",
      data: {page: 'save', date: date, date2: date2, name: name, apt: apt, sug: sug, email: email},
      type: 'post',
      success: function(data) {
        console.log(data);
        $("#after").html("<h3>Your suggestion has been posted</h3><p>To post another suggestion refresh the page.</p>")
        .show();
      },
      error: function(err) {
        console.log(err);
      }
    });

    $("form").hide();
    return false;
  });

  $("#reset").click(function() {
    location = 'index.php?page=reset';
  });
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

list($fname, $lname, $apt, $email) = explode(',', $_COOKIE['login']);
$name = "$fname $lname";
$date2 = date("l, F j, Y"); // the show date.
$date = date("Y-m-d"); // the hidden date and the date in the span.

echo <<<EOF
$top
<h2>Hello $name</h2>
<p>You live in apartment $apt.</p>
<div id='after'>
<p>This is the website for the <b>Independendent Living</b> group at Courtyards.
You can leave suggestions here. If this is about the dinning hall please indicate the meal.
For example: Dinner: your comment.</p>
</div>
<form>
<table>
<tr>
<td>Date:</td><td><span>$date2</span><span class="hide">$date</span></td>
</tr>
<tr>
<td>Suggestion:</td><td><textarea autofocus required></textarea></td>
</tr>
<tr>
<td colspan="2"><input type='submit' value='Submit' id='submit'></td>
</tr>
</table>
<input type='hidden' id='date2' value='$date2'> <!--Show Date-->
<input type='hidden' id='date' value='$date'> <!--Y-m-d-->
<input type='hidden' id='name' value='$name'>
<input type='hidden' id='apt' value='$apt'>
<input type='hidden' id='email' value='$email'>
</form>
<button id='reset'>Reset Your Info</button>
$footer
EOF;

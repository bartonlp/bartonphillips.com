<?php
// Send an email about volunteering
// This is an Ajax responder

// usage
// vMailer.ajax.php?event=$event&subject=$subject&mailto=$mailto
// event: the event being volunteered for. For example 'Earth+Day'.
// subject: the subject of the email. If missing then use the 'event'
// mailto: the eamil address to mail to. If missing then mail to
// bartonphillips@gmail.com for now

define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new PokerClub;
  
$referer = $_SERVER['HTTP_REFERER'];

// Don't let people come here from anywhere else than the members
// page! We can change this later to make it only our sites

if(!preg_match("{^http://www.bartonphillips}i", $referer)) {

  echo <<<EOL
<html>
<body>
<h1>This page can only be accessed indirectly from this domain.</h1>
<p>Please return to our <a href='/index.php'>home page</a> link.</p>
<p>Referer=$referer</p>
</body></html>
EOL;

  exit();
}

$S->query("update pokermembers set canplay='yes' where id='$S->id'");

header("plain/text");

if(empty($S->id)) {
  // This is an error big time!
  echo "NO ID. You must login before you can signup for poker night.\n";
  exit;
}

// This should work with GET or POST

if(count($_GET)) {
  extract($_GET);
} else if(count($_POST)) {
  extract($_POST);
} else {
  // with post this can only happen if the form has no <input text
  // etc. elements. That is only a submit.
  // With get it means someone forgot to add the params to the url.
  
  echo "Error No INFO passed in";
  exit;
}

if(empty($event)) {
  // use the referer as the event.

  $event = $referer;
}

if(empty($subject)) {
  $subject = $event;
}

if(empty($mailto)) {
  $mailto = 'Barton Phillips <bartonphillips@gmail.com>';
}

$S->query("select FName, LName from pokermembers where id='$S->id'");

@extract($S->fetchrow('assoc'));

$name = ${FName} . ${LName};
if(empty($name)) {
  echo "bad:: Name empty not valid";
  exit;
}

$message = "$FName $LName will be there: $event. ";

$ret = mail($mailto, $subject, $message, 'from: barton@bartonphillips.org');

if($ret) {
  echo "ok:: mailto='$mailto', subject='$subject', message='$message'";
} else {
  echo "bad:: mailto='$mailto', subject='$subject', message='$message'";
}

?>  
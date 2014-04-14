<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new PokerClub;
$self = $_SERVER['PHP_SELF'];

$id = $_GET['id'];
$canplay = $_GET['attend'];
$date = $_GET['date'];
$date = " on $date";

$response = $canplay == "yes" ? "Will" : "Will Not";
if(!$id) {
  echo "<h1>NO ID ERROR</h1>\n";
  exit();
}

// Set the cookie also

$S->SetIdCookie($id);

$n = $S->query("select concat(FName, ' ', LName) from pokermembers where id='$id'");
if(!$n) {
  echo "<h1>ID=$id, Not found in database. ERROR</h1>\n";
  exit();
}

list($name) = $S->fetchrow('num');

$h->title = "Granby Poker Club Invitation - Response";
$h->banner = "<h1>Response to Invitation</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>Thank you $name, you said you would <b>$response</b> attend Poker Night$date.</p>
<a href="index.php">Return to Main Page</a>
</body>
</html>
EOF;

$msg = "$name $response attend Poker Night$date";

mail("bartonphillips@gmail.com", "Poker Invite Response", $msg, "From: bartonphillips@gmail.com");

$S->query("update pokermembers set canplay='$canplay' where id='$id'");
?>
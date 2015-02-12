<?php
// BLP 2014-03-06 -- ajax for tracker.js

//ini_set("error_log", "/tmp/debugblp.txt");

require_once("/var/www/includes/siteautoload.class.php");
Error::setNoEmailErrs(true); // For debugging
Error::setDevelopment(true); // during development
Error::setErrorType(E_ALL & ~(E_WARNING | E_NOTICE | E_STRICT));

$S = new Database($dbinfo);

/*
CREATE TABLE `tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(255) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `difftime` time DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=latin1
*/

if($_POST['page'] == 'load') {
  $self = $_POST['self'];
  $referrer = $_POST['referrer'];
  
  $ip = $_SERVER['REMOTE_ADDR'];
  $agent = $_SERVER['HTTP_USER_AGENT'];
  $here = $_SERVER['PHP_SELF'];

  $S->query("insert into tracker (page, ip, agent, starttime, referrer) ".
            "values('$self', '$ip','$agent', now(), '$referrer')");
  $id = $S->getLastInsertId();
  
  echo "$id";
  exit();
}

if($_POST['page'] == 'unload') {
  $id = $_POST['id'];
  if(!$id) {
    throw new Exception("tracker.php: NO ID in unload");
  }
  $S->query("update tracker set endtime=now(), difftime=timediff(now(),starttime) where id=$id");
  echo "Good By";
  exit();
}

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h1>Go Away!</h1>
</body>
</html>
EOF;

<?php
// This is a server side example

// Start a PHP Session

session_start(); 

require_once("dbclass.connectinfo.i.php"); // has $Host, $User, $Password

// This file has the MySqlSlideshow class

require_once("mysqlslideshow.class.php");

// Construct the slideshow class:
// There is a 4th argument for the database name if not "mysqlslideshow" and a 5th argument for the table name if not
// "mysqlslideshow"

$ss = new MySqlSlideshow($Host, $User, $Password, $Database, $Table); // use values from dbclass.connectinfo.i.php

// for use in <form action="$self" tags.

$self = $_SERVER[PHP_SELF];

// Check for Microsoft Internet Explorer -- because everything Microsoft makes is broken!

$isIe = preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']);
$ieMsg = $isIe ? '<p style="color: red">Microsoft Internet Explorer Version, because Microsoft can not do it like anyone else!</p>' : "";

// This file should not be in the Apache path for security reasons   
//********************
// Start of Slide Show Logic

// NOTE THE ORDER. STOP must be before START and session checking. One could of course design this section differently so the
// order was not important but this is only an example of using the CLASS.

if($_POST[stop]) {
  unset($_SESSION[next]);
  echo <<<EOF
<html>
<head>
  <title>Slideshow Example: Server Side Version</title>
</head>
<body>
<h1>Slide Show Example: Server Side Version</h1>
$ieMsg
<form action="$self" method="post">
<input type="submit" name="start" value="Start"/>
</form>
</body>
</html>
EOF;
  exit();
}

if($_POST[start]) {
  $_SESSION[next] = "next";
}

if($_SESSION[next] == "next") {
  $inx = $_SESSION[index];
  $ids = $_SESSION[ids];

  // Microsoft test
  
  if(!isIE) {
    $images = $_SESSION[images];
  
    // getImage() returns an assoc array [data], [mime], [subject], and [desc]
    // [data] is base64 by default. If a second argument is provided as 'raw' then the data is the raw image data.

    $data = $images[$inx++];
    $_SESSION[index] = ($inx > count($ids)-1) ? 0 : $inx;
  
    $image = $data[data]; // image in base64
    $mime = $data[mime]; // mime type like "image/gif" etc.
    $subject = $data[subject]; 
    $desc = $data[desc];
  } else {
    // Handle BROKEN Browser!
    
    $image = "mysqlslideshow.php?image=$ids[$inx]&type=raw";
    $info = $ss->getInfo($ids[$inx++]);
    $_SESSION[index] = ($inx > count($ids)-1) ? 0 : $inx;
    $subject = $info[subject];
    $desc = $info[description];
  }
  
  echo <<<EOF
<html>
<head>
  <title>Slideshow Example: Server Side Version</title>
  <!-- Set Refresh for every 5 seconds -->
  <meta http-equiv="Refresh" content="5"; url="http://localhost/test.php" />
</head>
<body>
<h1>Slide Show Example: Server Side Version</h1>
$ieMsg
<form action="$self" method="post">
<input type="submit" name="stop" value="Stop"/>
</form>
<p>Image: $inx</p>
<img src="$image" alt="" /><br>
<p>Subject: $subject, Description: $desc</p>
</body>
</html>
EOF;
  exit();
}

//++++++++++++++++++++

// First Page

// Get a list of id's.
// This function takes one optional arguments:
// $where: defaults to "", the where conditions of the query.
// You can add where conditions like this for example: "type = 'link' && data like('%bill%.jpg')" then you
// would only get link type rather than data type entries and only links with the name bill and jpegs.

$ids = $ss->getImageIds();

// Set the session up. 

$_SESSION[ids] = $ids; 
$_SESSION[index] = 0; // Start at the beginning

// Get all the images
// For NON IE browsers we can cache the images.

if(!$isIe) {
  $images = Array();

  for($i=0; $i < count($ids); ++$i) {
    $images[$i] = $ss->getImage($ids[$i]);
  }
  $_SESSION[images] = $images;
}

// Here is the example of a slide show.
// We have two buttons "Start" and "Stop"
// and the table with the slideshow mysqlslideshow table displayed (not everything just some).
//
// The style is to add borders and padding to the table.
//

echo <<<EOF
<html>
<head>
<style type="text/css">
#displayAllImageInfo * {
  border: 1px solid black;
  padding: 0 10px;
}
</style>
</head>

<body>
<h1 id="maintitle">Slide Show Example: Server Side Version</h1>
$ieMsg
<div id="startstop">
<form action="$self" method="post">
<input type="submit" name="start" value="Start"/>
</form>
</div>

EOF;

$ss->displayAllImageInfo();

echo <<<EOF
  </body>
</html>
EOF;
?>

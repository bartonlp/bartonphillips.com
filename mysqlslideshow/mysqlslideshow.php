<?php
// This is intended for use with Javascript Ajax and as the subject to the "<img src=" or as the subject to a Javascript sequence
// like "var i = new Image(); i.src=mysqlslideshow.php?image=2;"

require("dbclass.connectinfo.i.php"); // has $Host, $User, $Password  
require("mysqlslideshow.class.php");

$ss = new MySqlSlideshow($Host, $User, $Password);
  
//********************
// This is an Ajax call.
// Get the image given an id.
// The arguments are:
// image=id
// type=raw optional argument, if pressent then the raw image data is returned with the proper mime type header. If not present
//   then returns an echoed data package that can be the argument to "<img src=" tag.
// addinfo=1 optional argument, if present and if not specified as raw then the "<::subject::>text<::description::>text" is
// appended onto the base64 image packet. If type=raw then addinfo is ignored!

if($_GET['image']) {
  extract($_GET);
  
  if($type == 'raw') {
    $ar = $ss->getImage($image, ""); // the second arg defaults to base64 so here we want to unset it.
    Header("Content-type: $mime");
    $data = $ar['data'];
    echo $data;
  } else {
    $ar = $ss->getImage($image); // default is base64
    Header("Content-type: text/plain");
    $data = $ar['data'];
    $data = "$data";
    
    if($addinfo) {
      $data .= "<::subject::>$ar['subject']<::description::>$ar['desc']";
    }
    echo "$data";
  }
  exit();
}

//********************
// This is an Ajax call.
// Gets the "subject" and "description"
// The arguments are:
// info=id
// returns an echoed string like this:
// "<::subject::>subject info<::description::>>description info"
// this string can be parsed to get the subject and description.

if($id = $_GET['info']) {
  Header("Content-type: text/plain");
  $data = $ss->getInfo($id);
  echo "<::subject::>$data['subject']<::description::>$data['description']";
  exit();
}

//********************
// This is an Ajax call.
// Get the list of ids
// ids=1
// where=where arguments. optional
// returns the list of ids

if($_GET['ids']) {
  Header("Content-type: text/plain");
  $ar = $ss->GetImageIds($_GET['where']);
  $ids = "";
  foreach($ar as $id) {
    $ids .= "$id,";
  }

  echo rtrim($ids, ',');
  exit();
}

//********************
// This is an Ajax call.
// Display a <table> containing all of the rows of the database table
// table=1

if($_GET['table']) {
  Header("Content-type: text/plain");
  return $ss->displayAllImageInfo();
}

?>
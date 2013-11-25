<?php
// Add or Update an Image in the Database table

// This file should not be in the Apache path for security reasons   
require_once("dbclass.connectinfo.i.php"); // has $Host, $User, $Password

// This file has the MySqlSlideshow class

require_once("mysqlslideshow.class.php");

// Construct the slideshow class:
// There is a 4th argument for the database name if not "mysqlslideshow" and a 5th argument for the table name if not
// "mysqlslideshow"

$ss = new MySqlSlideshow($Host, $User, $Password); // use values from dbclass.connectinfo.i.php
   
//********************
// The following section is used to add images and text to the database
// Options to URL:
// ?image=imagefilename&subject=subject&desciption=description
// only image is required.
// ?update=id&subject=subject&desciption=description
// only update id is required but one would think that ether or both additonal arguments would make more sense
// With NO arguments slide show plus display table of image info.

if($image = $_GET['image']) {
  // Add a new image to the table.
  
  $subject = $_GET['subject'];
  $desc = $_GET['description'];
  $type = $_GET['type'] ? $_GET['type'] : 'link';
  
  if(($ret = $ss->addImage($image, $subject, $desc, $type)) !== true) {
    echo "$ret<br>";
  } else {
    echo "<br>Image=$image<br>Added<br>";
  }
  exit();
}

// Update an existing images subject and description
if($id = $_GET['update']) {
  // Update an existing image to change the subject and/or the description
  
  $subject = $_GET['subject'];
  $desc = $_GET['description'];

  $ret = $ss->updateImageInfo($id, $subject, $desc);
  if($ret === true) {
    echo "<br>Image with id=$id has been updated with<br>subject=$subject,<br>and description=$desc<br>\n";
  } else {
    echo "$ret<br>";
  }
  exit();
}
// End of the add and update logic
//********************
?>
<html>
<body>
<h1>Usage</h1>
<p>This page accepts several GET arguments.</p>
<code>addupdateimage.php?image=imagefile&amp;subject=text&amp;description=text</code>
<p>The first argument &quot;image&quot; is the filename of the iage file to add to the database table</p>
<p>The second and third arguments are optional and update the <i>subject</i> and <i>description</i>
   fields in the database table.</p>
<code>addupdateimage.php?update=5&amp;subject=text&amp;description=text</code>
<p>The first argument &quot;update&quot; is the <i>id</i> of the image in the database table</p>
<p>The second and third arguments are optional and update the <i>subject</i> and <i>description</i>
   fields in the database table.</p>
</body>
</html>


<?php
$errorhdr = <<<EOF
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;
  
if(empty($_SERVER["HTTP_REFERER"])) {
  echo <<<EOF
$errorhdr
<body>
You got here by accident! <a href=''> Return to welcome page</a><br>
</body>
</html>
EOF;
  exit();
}

// File and new size
$filename = $_GET['image'];
$imgwidth = $_GET['width'];
$imgheight = $_GET['height'];
$imgpercent = $_GET['percent'];

header('Content-type: image/jpg');

$newwidth = $imgwidth;
$newheight = $imgheight;

list($width, $height) = getimagesize($filename);

if(!empty($imgpercent)) {
  $imgpercent /= 100;
  // Get new sizes
  $newwidth = $width * $imgpercent;
  $newheight = $height * $imgpercent;
} else {
  if(!empty($imgwidth) && empty($imgheight)) {
    $newwidth = $imgwidth;
    $newheight = $height * $imgwidth/$width;
  } elseif(!empty($imgheight) && empty($imgwidth)) {
    $newheight = $imgheight;
    $newwidth = $width * $imgheight/$height;
  }
}
// Load
$thumb = imagecreatetruecolor($newwidth, $newheight);
$source = imagecreatefromjpeg($filename);

// Resize
imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

// Output
imagejpeg($thumb);
?>
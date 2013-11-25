<?php
// Add selected images from a directory

// This file should not be in the Apache path for security reasons   
require_once("dbclass.connectinfo.i.php"); // has $Host, $User, $Password

// This file has the MySqlSlideshow class

require_once("mysqlslideshow.class.php");

// Construct the slideshow class:
// There is a 4th argument for the database name if not "mysqlslideshow" and a 5th argument for the table name if not
// "mysqlslideshow"

$ss = new MySqlSlideshow($Host, $User, $Password, $Database, $Table); // use values from dbclass.connectinfo.i.php

$self = $_SERVER['PHP_SELF'];

if($_POST) {
  extract($_POST);

  echo <<<EOF
<html>
<body>
<h1>Adding Images</h1>

EOF;
  for($i=0; $i < $count; ++$i) {
    $image = eval("return \$box$i;");
    if($image) {
      $subject = eval("return \$subject$i;");
      $desc = eval("return \$desc$i;");
      if(($ret = $ss->addImage($image, $subject, $desc, $type)) === true) {
        echo "<p>Image added: $image, subject=$subject, description=$desc</p>\n";
      } else {
        echo "<p style='color: red'>$ret</p>\n";
      }
    }
  }
  echo <<<EOF
</body>
</html>
EOF;
  exit();
}

if($path = $_GET['path']) {
  $type = $_GET['type'] ? $_GET['type'] : 'link';
  
  $ar = glob($path);
  $images = Array();

  $pattern = $_GET['pattern'];

  if(!empty($pattern)) {
      foreach($ar as $file) {
      if(preg_match("/$pattern/", $file)) {
          $images[] = $file;
      }
    }
  } else {
    $images = $ar;
  }
  if(count($images)) {
    echo <<<EOF
<html>
<body>
<h1>Select Images</h1>
<form action="$self" method="post">

EOF;
    $i=0;
    for(; $i < count($images); ++$i) {
      $image = $images[$i];
      echo <<<EOF
<input type="checkbox" name="box$i" value="$image"/>$image<br>
<input type="text" name="subject$i" /><br>
<input type="text" name="desc$i" /><br>
<br>
EOF;
    }
    echo <<<EOF
<input type="hidden" name="count" value="$i" />
<input type="hidden" name="type" value="$type" />
<input type="submit" value="Submit"/>
</form>
</body>
</html>
EOF;
  } else {
    echo <<<EOF
<html>
<body>
<h1>No Files Matched</h1>
</body>
</html>
EOF;
  }
}
?>
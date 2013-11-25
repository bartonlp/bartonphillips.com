<?php
// This class facilitates a slide show where the links or image data is stored in a mysql table. In addition to the link/data the
// table comments, description,  date, etc.
//
// The class provides methods for getting, displaying, editing, and adding image items

// This class extends a Database class that handles the MySql connection and provides at a minimum:
// Database::__construct; Conects and opens database.
// Database::query; Dose a mysql_query.
// Database::getDb; return the resource from mysql_connect().

// The database should have the following fields:
// CREATE TABLE `mysqlslideshow` (
//  `id` int(11) NOT NULL auto_increment,
//  `type` enum('link', 'data') default 'link', /* if link then data is a link address, if data then image.
//  `imageinfo` varchar(255) NOT NULL, /* return from getimagesize(img, extrinfo) serialized
//  `subject` varchar(255) default NULL,
//  `description` text,
//  `data` blob default NULL,
//  `created` datetime NOT NULL default CURRENT_TIMESTAMP,
//  PRIMARY KEY  (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//

// include a database class file.
// what I do is have a file that is not accessable by the web server but can be loaded via require_once().
// In this file I have the user, password so these are secure.

require_once("dbclass.i.php"); // name of your Database class file.

class MySqlSlideshow extends Database {
  private $result =  0;
  private $table;
  private $numRows = -1; // uninited

  // Constructor take three manditory and two optional arguments
  
  public function __construct($host, $user, $password, $database="mysqlslideshow", $table="mysqlslideshow") {
    // Constructor
    $this->table = $table;

    // Call the Database constructor
    
    parent::__construct($host, $user, $password, $database); // open the database
  }

  //********************
  // These are the primary methods used for a slide show
  //********************
  
  // Get list if ids given an optional where argument
  // returns an array of ids
  // false if error.
  
  public function getImageIds($where="") {
   if(isset($whre)) {
      //echo "where=$where";
      $where = " where $where";
    }
    $result = $this->query("select id from $this->table$where");
    if(!$result) {
      return false;
    }
    
    while($row = mysql_fetch_assoc($result)) {
      $ids[] = $row['id'];
    }
    return $ids;
  }
  
  // Get the image given an id
  // Arguments:
  // $id: the id of the image in the database table
  // $type: defaults to "base64". If $type is not "base64" then returns raw image data.
  // returns an assoc array:
  // [data] has string that can be used in a "<img src=" tag, or if $type is not "base64" raw image data
  // [mime] has the mime type of the image
  // [subject] has a string with the subject text
  // [desc] has a string with the description text
  
  public function getImage($id, $returntype="base64") {
    $result = $this->query("select * from $this->table where id='$id'");
    $row = mysql_fetch_assoc($result);

    extract($row);

    $ret = Array();
    
    if($type == "data") {
      $image = $data;
    } else {
      $image = file_get_contents($data);
    }

    $imageinfo = unserialize($row['imageinfo']);
    $mime = image_type_to_mime_type($imageinfo[2]);

    if($returntype == "base64") {
      $image = base64_encode($image);
      $ret['data'] = "data:$mime;base64,$image";
    } else {
       $ret['data'] = $image;
    }
    $ret['mime'] = $mime;
    $ret['subject'] = $subject;
    $ret['desc'] = $description;
    return $ret;
  }

  // Get the information (subject, and description)
  // $id is the entry to get (like getImage())
  // returns an assoc array with the two fields
  
  public function getInfo($id) {
    $result = $this->query("select * from $this->table where id='$id'");
    $row = mysql_fetch_assoc($result);

    extract($row);

    return Array('subject'=>$subject, 'description'=>$description);
  }
    
  //********************
  // These are posibley usefull methods but are now shown in the EXAMPLE supplied
  //********************

  // Quiry database.
  // $where defaults to blank. Put the where conditions in $where
  // returns true or false
  
  public function imageQuery($where="") {
    if(isset($whre)) {
      echo "where=$where";
      $where = " where $where";
    }
    // Note Database::query() can either just output an error message and exit,
    // or return false on error
    
    $result = $this->query("select * from $this->table$where");
    if(!$result) {
      return false;
    }
    $this->result = $result;
    $this->numRows = mysql_num_rows($result);
   
    return true;
  }

  // Return the $result for a query in case user wants to do direct mysql stuff.
  
  public function returnResult() {
    return $this->result;
  }

  // Get the next image from the database.
  // returns the row as an assoc array
  // or false if all items have been read. This can be used in a while statement like:
  // $x->imageQuery(some query);
  // while($row = $x->getNextImageRowData()) { do something }
  
  public function getNextImageRowData() {
    $row = mysql_fetch_assoc($this->result);
    return $row;    
  }

  // Get number of images in database
  // if this is called before a quiry then the method returns "false" so user should always check the return value with === or
  // !== false;
  
  public function getNumImages() {
    if($this->numRows == -1) {
      return false;
    }
    return $this->numRows; // This could be zero so don't be sure to test === or !== false to see if an error ocurred   
  }

  //********************
  // The next two methods are used to add and modify the database table. See examples in the EXAMPLE file
  //********************
  
  // Add an image to the databasse table. This does an insert.
  // Arguments:
  // $imagefile: either the path on the server of the image file, or if $type='data' the actual image data.
  // $type: defaults to 'link'. Can be 'data' if the $imagefile is the actual image data.
  // $subject: defaults to blank.
  // $description: defaults to blan.
  // returns true if OK else an error string.

  public function addImage($imagefile, $subject="", $desc="", $type="link") {
    if(!file_exists($imagefile)) {
      return "File does not exist: $imagefile";
    }

    $imagefile = realpath($imagefile); // make it into the absolute path
    
    $extrainfo;
    $imageinfo = getimagesize($imagefile, $extrainfo);
    array_push($imageinfo, $extrainfo);
    
    $imageinfo = serialize($imageinfo);

    switch($type) {
      case "link":
        $data = $imagefile;
        break;
      case "data":
        $data = file_get_contents($imagefile);
        break;
      default:
        // Error
        return "Invalid type: $type";
    }

    //$imageinfo = mysql_real_escape_string($imageinfo);
    
    $query = "insert into $this->table (type, imageinfo, data";
    $qdata = ") value('$type', '$imageinfo', '$data'";
    
    if($subject) {
      $query .= ", subject";
      $qdata .= ", '$subject'";
    }
    if($desc) {
      $query .= ", description";
      $qdata .= ", '$desc'";
    }
    $query .= ", created$qdata, now())";

    //echo "$query<br>";
    $this->query($query);

    return true;
  }

  // update an image whose id=$id. Set the subject and desc
  
  public function updateImageInfo($id, $subject, $desc) {
    $ids = $this->getImageIds();  // get an array of all the ids 

    // Is this id in the field?
    if(array_search($id, $ids) === false) {
      return "Id=$id is not in the table";
    }
    
    $s = "";
    if($subject) {
      $s = " subject='$subject'";
    }
    $d = $s ? ", " : "";
    
    if($desc) {
      $d .= "description='$desc'";
    }
    $query = "update $this->table set $s$d where id='$id'";
    //echo "$query<br>";

    $this->query($query);

    if(!mysql_affected_rows()) {
      return "No changes made by query: $query";
    } else {
      return true;
    }
  }

  //********************
  // A helper method to display a <table> with all of the items in the database table
  //********************
  
  // Display all of the information in the table
  
  public function displayAllImageInfo() {
    $result = $this->query("select id, subject, description, data, created from $this->table");
    echo <<<EOF
<table id="displayAllImageInfo">
<thead>
<tr>

EOF;
    $header = true;

    while($row = mysql_fetch_assoc($result)) {
      if($header) {
        $header = false;
        $line = "<tr>\n";
        foreach($row as $key=>$value) {
          echo "<th>$key</th>\n";
          $item = $value ? $value : "&nbsp;";
          $line .= "<td>$item</td>\n";
        }
        echo "</tr>\n</thead>\n<tbody>\n";
        echo "$line</tr>\n";
      }
      echo "<tr>\n";
      foreach($row as $value) {
        $item = $value ? $value : "&nbsp;";
        echo "<td>$item</td>\n";
      }
      echo "</tr>\n";
    }
    echo <<<EOF
</tbody>
</table>

EOF;
  }
}
// !!! There can be NOTHING after the ending PHP tag NOT EVEN a NL or CR!!!
?>
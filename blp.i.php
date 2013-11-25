<?php
// Site class file for bartonphillips.com

require_once("/home/bartonlp/includes/site.class.php");

class Blp extends SiteClass {

  public $blpIp;
  
  /**
   * Constructor
   *
   * @param array|object $s
   *  fields: host, user, password, database, siteDomain, subDomain, memberTable, headFile,
   *  bannerFile, footerFile, nodb, count, daycountwhat, emailDomain these fields are all protected.
   *  If there are more elements in $s they become public properties.
   * Defaults are read in to $s from blp.conf.php
   * $x defaults to a null array but it can be overriden by either an array or an object.
   *  If I make $x=null the the foreach() gives an error if nothing is passed in.
   */
  
  public function __construct($x=array()) {
    // include the $s array
    // NOTE 11/15/2012: added $x and foreach() to allow me to change the $s values or add to them.

    require("/home/bartonlp/includes/blp.conf.php");
    $s['count'] = true; // default to true
    $s['myUri'] = "bartonphillips.dyndns.org";
    
    // If $x has values then add/modify the $s array
    
    if(!is_null($x)) foreach($x as $k=>$v) {
      $s[$k] = $v;
    }
    parent::__construct($s);
    $this->blpIp = $this->myIp;
  }

  public function isBlp() {
    return $this->isMe();
  }

  // Override to add msg addition for when counter was reset

  public function getFooter(/* mixed */) {
    $args = func_get_args();
    $n = func_num_args();
    $arg = array();

    if($n == 1) {
      $a = $args[0];
      
      if(is_string($a)) {
        $arg['msg'] = $a;
      } elseif(is_object($a)) {
        //echo "IS OBJECT<br>\n";
        foreach($a as $k=>$v) {
          //echo "(k=$k) $k=$v<br>\n";
          $arg[$k] = $v;
        }
      } elseif(is_array($a)) {
        $arg = $a;
      } else {
        // If called from getPageTopBottom($h, $b) then $b will be there even though there is nothing in it.
        //throw(new Exception("Error: getFooter() argument no valid: ". var_export($a, true)));
      }
    } elseif($n > 1) {
      $keys = array(msg, w3cmsg, ctrmsg, google);
      $ar = array();
      for($i=0; $i < $n; ++$i) {
        $ar[$keys[$i]] = $args[$i];
      }
      $arg = $ar;
    }

    $arg['msg'] = $arg['msg'] . "<p style='text-align: center'>Counter Reset 2012-11-04</p>";

    $arg['google'] = false;
    
    return parent::getFooter($arg);
  }
}

// Callback to get the user id for db.class.php SqlError

if(!function_exists('ErrorGetId')) {
  function ErrorGetId() {
    $id = "IP=$_SERVER[REMOTE_ADDR], AGENT=$_SERVER[HTTP_USER_AGENT]";
    
    return $id;
  }
}

// WARNING THERE MUST BE NOTHING AFTER THE CLOSING PHP TAG.
// Really nothing not even a space!!!!

?>
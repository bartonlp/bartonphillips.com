<?php
// Site class file for bartonphillips.com

class Blp extends SiteClass {
  public $blpIp;
  
  /**
   * Constructor
   *
   * @param array|object $x
   *  fields: host, user, password, database, siteDomain, subDomain, memberTable, headFile,
   *  bannerFile, footerFile, nodb, count, daycountwhat, emailDomain these fields are all protected.
   *  If there are more elements in $s they become public properties.
   * $x defaults to a null array but it can be overriden by either an array or an object.
   *  If I make $x=null the foreach() gives an error if nothing is passed in.
   */
  
  public function __construct($x=array()) {
    global $dbinfo, $siteinfo; // from .sitemap.php

    //Error::setNoEmailErrs(true); // For debugging
    //Error::setDevelopment(true); // during development
    
    $s = $siteinfo; // from .sitemap.php
    $s['databaseClass'] = new Database($dbinfo);
    
    //$s['count'] = true; // default to true
    //$s['myUri'] = "localhost"; //"bartonphillips.dyndns.org";
    
    // If $x has values then add/modify the $s array
    
    if(!is_null($x)) foreach($x as $k=>$v) {
      $s[$k] = $v;
    }

    parent::__construct($s);

    $this->blpIp = $this->myIp;
    $this->query("insert ignore into blpip values('$this->blpIp', now())");
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
      $keys = array(msg, w3cmsg, ctrmsg);
      $ar = array();
      for($i=0; $i < $n; ++$i) {
        $ar[$keys[$i]] = $args[$i];
      }
      $arg = $ar;
    }

    $arg['msg'] = $arg['msg'] .
                  "<p style='text-align: center'>Counter Reset 2012-11-04</p>";

    return parent::getFooter($arg);
  }

  protected function daycount($inc) {
    $sql = "insert into counter2 (date, filename, count) values(now(), '$this->self', 1) ".
           "on duplicate key update count=count+1";

    $this->query($sql);
    parent::daycount($inc);
  }
}

// Callback to get the user id for SqlError

if(!function_exists('ErrorGetId')) {
  function ErrorGetId() {
    $id = "IP=$_SERVER[REMOTE_ADDR], AGENT=$_SERVER[HTTP_USER_AGENT]";
    
    return $id;
  }
}

// WARNING THERE MUST BE NOTHING AFTER THE CLOSING PHP TAG.
// Really nothing not even a space!!!!

?>
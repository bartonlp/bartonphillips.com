<?php

class PokerClub extends SiteClass {
  //----------------------
  // Class Constructor
  //----------------------
  
  public function __construct($x=array()) {
    $site = preg_replace("/www./", '', $_SERVER['SERVER_NAME']); // Because this could be .com or .org
    
    $s = array(dbhost=>'localhost:3306', dbuser=>'3342', dbpassword=>'lueQu5saig2l', dbdatabase=>'bartonphillipsdotorg',
               count=>true,
               siteDomain=>"$site",
               subDomain=>"/pokerclub", // the sub domain part of the setcookie(), the 4th parameter
               memberTable=>"pokermembers",
               headFile=>"/home/bartonlp/bartonphillips.com/htdocs/pokerclub/poker.head.i.php",
               bannerFile=>"/home/bartonlp/bartonphillips.com/htdocs/pokerclub/poker.banner.i.php",
               daycountwhat=>"all"
              );
    
    foreach($x as $k=>$v) {
      $s[$k] = $v;
    }

    parent::__construct($s);
  }

  /*
  public function setIdCookie($id) {
    echo "id=$id<br>";
    return parent::setIdCookie($id);
  }
  */
  
  // Called by the constructor
  
  public function checkId() {
    $id = $_COOKIE['PId'];
    if(!isset($id)) {
      $id = $_COOKIE['SiteId']; // New logic in site.class.php uses SiteId
      if(!isset($id)) {
        return 0;
      }
    }
    return parent::checkId($id);
  }

  public function getUser() {
    return "$this->fname $this->lname";
  }

  public function getFooter($b=null) {
    if(is_string($b)) {
      $x->msg1 = $b;
    } else {
      $x = $b;
    }
    return parent::getFooter($x);
  }
}

// Callback to get the user id for SqlError

if(!function_exists(ErrorGetId)) {
  function ErrorGetId() {
    $id = "IP={$_SERVER['REMOTE_ADDR']}, AGENT={$_SERVER['HTTP_USER_AGENT']}";
    
    return $id;
  }
}

?>
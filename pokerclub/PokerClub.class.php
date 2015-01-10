<?php
define(ID_BARTON, 10);

class PokerClub extends SiteClass {
  //----------------------
  // Class Constructor
  //----------------------
  
  public function __construct($x=array()) {
    global $dbinfo, $siteinfo; // from .sitemap.php

    Error::setNoEmailErrs(true); // For debugging
    Error::setDevelopment(true); // during development
    Error::setErrorType(E_ALL & ~(E_WARNING | E_NOTICE | E_STRICT));
    //Error::setNoOutput(true); // no output to user


    $site = preg_replace("/www./", '', $_SERVER['SERVER_NAME']); // Because this could be .com or .org
    $s = $siteinfo;
    $s['databaseClass'] = new Database($dbinfo);

    // If $x has values then add/modify the $s array

    if(!is_null($x)) foreach($x as $k=>$v) {
      $s[$k] = $v;
    }

    parent::__construct($s);
  }

  public function setIdCookie($id) {
    return parent::setIdCookie($id, 'PokerClub');
  }
  
  // Called by the constructor
  
  public function checkId() {
    return parent::checkId(null, 'PokerClub');
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
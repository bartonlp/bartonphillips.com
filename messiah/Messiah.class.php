<?php
class Messiah extends SiteClass {
  public function __construct($x=array()) {
    global $dbinfo, $siteinfo; // from .sitemap.php

    $s = $siteinfo; // from .sitemap.php
    $s['databaseClass'] = new Database($dbinfo);

    $s['siteDomain'] = "mountainmessiah.com";
    $s['memberTable'] = "messiah";
    $s['headFile'] = "/home/bartonlp/bartonphillips.com/htdocs/messiah/header.i.php";
    $s['bannerFile'] = "/home/bartonlp/bartonphillips.com/htdocs/messiah/banner.i.php";

    $s['emailDomain'] = "bartonphillips.com"; // Where do we send webmaster email


    if(!is_null($x)) foreach($x as $k=>$v) {
      $s[$k] = $v;
    }

    parent::__construct($s);
  }

  // Don't do any daycount or tracker
  
  protected function daycount($inc) {
  }

  // don't track anything

  protected function tracker() {
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
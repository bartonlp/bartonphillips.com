<?php
// Is a Bot?
// The class looks for a bot via the user agent string and a database of bots.
// The class uses one database and one table. You can use mysqli or sqlite3

define("DEFINES_VERSION", "1.0.0defines");

/* The mysql table looks like this:
CREATE TABLE `bots` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `site` varchar(255) NOT NULL,
  `page` varchar(255) NOT NULL,
  `agent` text NOT NULL,
  `count` int DEFAULT NULL,
  `type` int DEFAULT '4' COMMENT 'this is 1, 2, 4 or 0x100. Four is BOT_ISABOT. The BOT_types',
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`,`site`,`type`,`agent`(254))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

/*
  1 for 'robots.txt'
  2 for 'Sitemap.xml'
  4 for 'ISABOT' class.
  0x100 for tracker value 0 (curl type)
*/

define("BOT_ROBOT", 1);
define("BOT_SITEMAP", 2);
define("BOT_ISABOT", 4);
define("BOT_CRON_ZERO", 0x100);

// foundBotAs. This is the value in the tracker table as botAs.
define("BOTAS_MATCH", "match");
define("BOTAS_NOT", null);
define("BOTAS_ROBOT", "robot");
define("BOTAS_SITEMAP", "sitemap");
define("BOTAS_ZERO", "zero");
define("BOTAS_COUNTED", "counted");

// Class IsABot

class IsABot {
  private $host;
  private $user;
  private $database;
  private $password;
  private $db = null;
  private $result;
  private static $lastQuery;
  private static $lastNonSelectResult;
  private $ip;
  private $agent;
  private $site;
  
  public function __construct($site) {
    // Configure the database
    
    $this->host = "localhost";
    $this->user = "test";
    $this->database = "test";
    $this->password = require("/home/barton/database-password"); // Get the password from a secure location.
    // Alternatly just have it inline (not recommended).

    $this->ip = $_SERVER['REMOTE_ADDR'];
    $this->agent = $_SERVER['HTTP_USER_AGENT'];
    $this->site = $site;
    $this->opendb();
  }

  /*
   * isBot(string $agent):bool
   * Determines if an agent is a bot or not.
   * @return bool
   * Side effects:
   *  it sets $this->isBot
   *  it sets $this->foundBotAs
   * These side effects are used by checkIfBot():void see below.
   */
  
  public function isBot(string $agent):bool {
    $this->isBot = false;

    if(($x = preg_match("~\+*https?://|@|bot|spider|scan|HeadlessChrome|python|java|wget|nutch|perl|libwww|lwp-trivial|curl|PHP/|urllib|".
                        "crawler|GT::WWW|Snoopy|MFC_Tear_Sample|HTTP::Lite|PHPCrawl|URI::Fetch|Zend_Http_Client|".
                        "http client|PECL::HTTP~i", $agent)) === 1) { // 1 means a match
      $this->isBot = true;
      $this->foundBotAs = BOTAS_MATCH;

      $this->addToBots();
      
      return $this->isBot;
    } elseif($x === false) { // false is error
      // This is an unexplained ERROR
      throw new Exception("isbot.class.php: preg_match() returned false");
    }

    // If $x was 1 or false we have returned with true and BOTAS_MATCH or we threw an exception.
    // $x is zero so there was NO match.

    if($this->query("select robots from bots where ip='$this->ip'")) { // Is it in the bots table?
      // Yes it is in the bots table.

      $tmp = '';

      // Look at each posible entry in bots. The entries may be for different sites and have
      // different values for $robots.
      
      while([$robots] = $this->fetchrow('num')) {
        if($robots & BOT_ROBOT) {
          $tmp = "," . BOTAS_ROBOT;
        }
        if($robots & BOT_SITEMAP) {
          $tmp .= "," . BOTAS_SITEMAP;
        }
        if($robots & BOT_CRON_ZERO) {
          $tmp .= "," . BOTAS_ZERO;
        }
        if($tmp != '') break;
      }
      
      if($tmp != '') {
        $tmp = ltrim($tmp, ','); // remove the leading comma
        $this->foundBotAs = $tmp; //'bots table' plus $tmp;
        $this->isBot = true; // BOTAS_TABLE plus robot and/or sitemap
      } else {
        $this->foundBotAs = BOTAS_NOT;
        $this->isBot = false;
      }

      $this->addToBots();
      
      return $this->isBot;
    }

    // The ip was NOT in the bots table either.

    $this->foundBotAs = BOTAS_NOT; // not a bot
    $this->isBot = false;

    $this->addToBots();
    
    return $this->isBot;
  }

  // Private opendb method
  
  private function opendb() {
    if($this->db) return $this->db;
    
    $driver = new mysqli_driver();
    $driver->report_mode = MYSQLI_REPORT_OFF;

    $db = new mysqli($this->host, $this->user, $this->password, $this->database);

    if($db->connect_errno) {
      $this->errno = $db->connect_errno;
      $this->error = $db->connect_error;
      throw new Exception("isbot.class.php: Can't connect to database");
    }

    $db->query("set time_zone='EST5EDT'");
    $this->db = $db;
    $this->db->database = $database;
  }

  // Private Query method
  
  private function query($query) {
    $db = $this->db;

    self::$lastQuery = $query; // for debugging

    $result = $db->query($query);

    // If $result is false then exit
    
    if($result === false) {
      throw new Exception("isbot.class.php: error=$db->error, query=$query");
    }

    // result is a mixed result-set for select etc, true for insert etc.
    
    if($result === true) { // did not return a result object. NOTE can't be false as we covered that above.
      $numrows = $db->affected_rows;
      self::$lastNonSelectResult = $result;
    } else {
      // NOTE: we don't change result for inserts etc. only for selects etc.
      $this->result = $result;
      $numrows = $result->num_rows;
    }

    return $numrows;
  }

  private function fetchrow($result=null, $type="both") {
    if(is_string($result)) { // a string like num, assoc, obj or both
      $type = $result;
      $result = $this->result;
    } elseif(get_class($result) != "mysqli_result") { 
      throw new Exception("isbot.class.php: get_class() is not 'mysqli_result'");
    } 

    if(!$result) {
      throw new Exception("isbot.class.php: result is null");
    }

    switch($type) {
      case "assoc": // associative array
        $row = $result->fetch_assoc();
        break;
      case "num":  // numerical array
        $row = $result->fetch_row();
        break;
      case "obj": 
        $row = $result->fetch_object();
        break;
      case "both":
      default:
        $row = $result->fetch_array();
        break;
    }
    return $row;
  }

  private function addToBots() {
    $rob = BOT_ISABOT;

    $this->query("insert into bots (ip, site, page, agent, count, type, created, lasttime) ".
                 "values('$this->ip', '$this->site', '$this->self', '$this->agent', 1, $rob, now(), now()) ".
                 "on duplicate key update count=count+1, lasttime=now()");
  }
}
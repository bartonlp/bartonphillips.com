<?php
// BLP 2015-02-20 -- added 'copywrite' to siteinfo.  
// Site map
// This is included by the /siteautoload.php which is called from each page file.
// This file should only be loaded via the siteautoload.php file.
// This file has the sitemap which defines the various location where things can be found.
// DOC_ROOT and SITE_ROOT are defined by the siteautoload.class.php
// TOP is the path to the very top of the website. For example on digitalocean.com that is
// /var/www. All the sites are relative to TOP. The main include directory which has
// SiteClass.class.php is at TOP . "/includes/".
// SITE_INCLUDES is under the SITE_ROOT which is where we found the '.sitemap.php' file during
// our search in siteautoload.class.php. On digitalocean.com this would be something like
// '/var/www/bartonphillipsdotcom/'.
// After the four path defines we have defines for our LOGFILE and the email addresses used to
// send emails when errors occur.
// After the email defines we have two arrays, one for the database information ($dbinfo),
// and one for the site information ($siteinfo). These are used by the Database class and 
// SiteClass.class.php.
   
define('TOP', '/var/www'); // on lamphost.net this would be '/home/bartonlp'
define('INCLUDES', TOP."/includes");
define('DATABASE_ENGINES', INCLUDES."/database-engines");
//define('SITE_INCLUDES', SITE_ROOT."/includes"); // SITE_ROOT is defined in siteautoload.php!
define('SITE_INCLUDES', __DIR__ . "/includes");
// Email info and logfile location

//define('LOGFILE', "/tmp/database.log");

define('EMAILADDRESS', "bartonphillips@gmail.com");
define('EMAILRETURN', "bartonphillips@gmail.com");
define('EMAILFROM', "webmaster@bartonphillips.com");

// Database connection information
// 'engine' is the type of database engine to use. Options are 'mysqli', 'sqlite'. Others may be added
// later
           
$dbinfo = array('host' => 'localhost',
                'user' => 'barton',
                'password' => '7098653',
                'database' => 'bartonphillipsdotcom',
                'engine' => 'mysqli'
               );

// SiteClass information
// This site has no members so no membertable.
// See the SiteClass constructor for other possible values like 'count', 'emailDomain' etc.

$__year = date('Y');

$siteinfo = array('siteDomain' => "bartonphillips.com",
                  'siteName' => "Bartonphillips",
                  'mainTitle' => "Barton Phillips Home Page",
                  // 'emailDomain' => null,
                  'path' => '/var/www/bartonphillips',
                  'className' => "SiteClass",
                  'copyright' => "$__year Barton L. Phillips",
                  'author' => "Barton L. Phillips, mailto:bartonphillips@gmail.com",
                  //'memberTable' => "blpmembers",
                  'masterdb' => 'barton', // where bots, tracker and analysis are
                  'dbinfo' => $dbinfo,
                  'headFile' => SITE_INCLUDES . "/head.i.php",
                  'bannerFile' => SITE_INCLUDES . "/banner.i.php",
                  'footerFile' => SITE_INCLUDES . "/footer.i.php",
                  'count' => true,
                  'countMe' => true, // Count BLP
                  //'daycountwhat' => null,
                  'trackerImg1' => "/images/blp-image.png", // script
                  'trackerImg2' => "/images/146624.png", // normal
                  'myUri' => "bartonphillips.dyndns.org",
                  'EMAILADDRESS' => EMAILADDRESS,
                  'EMAILRETURN' =>EMAILRETURN,
                  'EMAILFROM' => EMAILFROM,
                 );

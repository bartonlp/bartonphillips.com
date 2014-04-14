<?php
   // For lamphost.net

   // Site map
   // This is included by the siteautoload.php which is called from each page file.
   // This file should only be loaded via the siteautoload.php file.
   // This file has the sitemap which defines the various location where things can be found.
   // DOC_ROOT, SITE_ROOT and TARGET_ROOT are defined by the siteautoload.php

   // TOP would be '/home/bartonlp' on lamphost.net because we keep the include file that are
   // common to all sites in the '/home/bartonlp/includes' directory which also has the
   // 'database-engines' directory.
   // SITE_INCLUDES is under the SITE_ROOT which is where we found the '.sitemap.php' file during
   // our search in siteautoload.php.
   // On lamphost.net this would be something like
   // '/home/bartonlp/bartonphillips.com/htdocs' and on our localhost it would be
   // '/var/www/bartonphillips.com/' which we would enter into the browser as
   // 'localhost/bartonphillips.com'
   // After the four path defines we have defines for our LOGFILE and the email addresses used to
   // send emails when errors occur.
   // After the email defines we have two arrays, one for the database information ($dbinfo),
   // and one for the site information ($siteinfo). These are used by the Database class and the
   // SiteClass.
   
define('TOP', '/home/barton11'); // on lamphost.net this would be '/home/bartonlp'
define('INCLUDES', TOP."/includes");
define('DATABASE_ENGINES', INCLUDES."/database-engines");
define('SITE_INCLUDES', SITE_ROOT."/includes"); // SITE_ROOT is defined in siteautoload.php!

// Email info and logfile location

define('LOGFILE', "/home/barton11/database.log");

define('EMAILADDRESS', "bartonphillips@gmail.com");
define('EMAILRETURN', "bartonphillips@gmail.com");
define('EMAILFROM', "webmaster@bartonphillips.com");

// Database connection information
// 'engine' is the type of database engine to use. Options are 'mysqli', 'sqlite'. Others may be added
// later
           
$dbinfo = array('host' => 'localhost',
                'user' => 'barton11_barton',
                'password' => '7098653',
                'database' => 'barton11_bartonphillipsdotorg',
                'engine' => 'mysqli'
               );

// SiteClass information
// This site has no members so no membertable.
// See the SiteClass constructor for other possible values like 'count', 'emailDomain' etc.

$siteinfo = array('siteDomain' => "bartonphillips.org",
                  //'memberTable' => "blpmembers",
                  'headFile' => SITE_INCLUDES."/head.i.php",
                  'bannerFile' => SITE_INCLUDES."/banner.i.php",
                  //footerFile => SITE_INCLUDES."/footer.i.php"
                  'count' => true,
                  'countMe' => true, // Count BLP
                  'myUri' => "bartonphillips.dyndns.org" // If we are local (at home) then 'localhost'
                 );

?>
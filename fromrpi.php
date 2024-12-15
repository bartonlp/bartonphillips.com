<?php
// BLP 2023-09-21 - Don't need this now that I have https: via Letsencrypt. See the README.md at
// https://bartonphillips.org:8000/certs/README.md
//
// Because all of the links are to bartonlp.org I can get the rpi index and just display it.
// REMEMBER you need a .eval not .php because the .php will have been already evaluated. The .eval
// can be a symlink.
// Also NOTE that if this where just file_get_contents("./somefile.php") the evaluation is not
// done. PHP comes with many built-in wrappers for various URL-style protocols for use with the
// filesystem functions such as fopen(), copy(), file_exists(), file_get_contents() and filesize().
// In addition to these wrappers, it is possible to register custom wrappers using the
// stream_wrapper_register() function.

require("../vendor/autoload.php"); // get the autoloader 
require("../vendor/bartonlp/site-class/includes/database-engines/helper-functions.php");

// Get the page from Rpi.
// This has to be named eval or anything that does not get run through a supplemental program like
// PHP.
// BLP 2023-09-21 - Changed from http to https

$page = file_get_contents("https://bartonphillips.org:8000/index.eval"); // This is a symlink to index.php

// write it out to a temp file
// This works because the bartonphillips.com directory has group www-date and rwx privilages.

file_put_contents("tempindex.php", $page);

// Get the mysitemap.json from the Rpi
// BLP 2023-09-21 - Changed from http to https

$_site = json_decode(stripComments(file_get_contents("https://bartonphillips.org:8000/mysitemap.json")));
if($_site === null) { echo "site null<br>"; exit("site null"); }

// Fix up the information so it works here.

$_site->dbinfo->host = "localhost";
$_site->headFile = "./includes/head.i.php";
$_site->bannerFile = "./includes/banner.i.php";
$_site->footerFile = "./includes/footer.i.php";

// Require the temp file now that we have $_site fixed up. The real index.php check to see if
// $_site is already available.

require("tempindex.php");

// Get rid of the evidence.

unlink("tempindex.php");

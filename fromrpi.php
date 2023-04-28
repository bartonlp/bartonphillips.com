<?php
// Because all of the links are to bartonlp.org I can get the rpi index and just display it.
// REMEMBER you need a .eval not .php because the .php will have been already evaluated. The .eval
// can be a symlink.
// Also NOTE that if this where just file_get_contents("./somefile.php") the evaluation is not
// done. PHP comes with many built-in wrappers for various URL-style protocols for use with the
// filesystem functions such as fopen(), copy(), file_exists(), file_get_contents() and filesize().
// In addition to these wrappers, it is possible to register custom wrappers using the
// stream_wrapper_register() function.

require("../vendor/autoload.php"); // get the autoloader 

// Function to strip comments from our json file.

function stripComments($x) {
  $pat = '~".*?"(*SKIP)(*FAIL)|(?://[^\n]*)|(?:#[^\n]*)|(?:/\*.*?\*/)~s';
  return preg_replace($pat, "", $x);
}

// Get the page from Rpi.

$page = file_get_contents("http://bartonphillips.org:8000/index.eval");

// write it out to a temp file

file_put_contents("tempindex.php", $page);

// Get the mysitemap.json from the Rpi

$_site = json_decode(stripComments(file_get_contents("http://bartonphillips.org:8000/mysitemap.json")));

//vardump("site", $_site);

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

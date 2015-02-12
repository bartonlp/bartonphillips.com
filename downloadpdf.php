<?php
// This is a proxy to be able to get at /tmp/pdfpage.pdf
//ini_set("error_log", '/tmp/debugblp.txt'); // BLP 2014-01-26 -- During debug
//error_log("in download.php");
header("Content-type: application/pdf");
$page = file_get_contents("/tmp/pdfpage.pdf");
echo $page;

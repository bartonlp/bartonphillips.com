<?php
// This is a proxy to be able to get at /tmp/pdfpage.pdf
header("Content-type: application/pdf");
$page = file_get_contents("/tmp/pdfpage.pdf");
echo $page;

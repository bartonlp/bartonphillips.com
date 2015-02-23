<?php
$pageHeadText = <<<EOF
<head>
  <title>$arg[title]</title>
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf8'>
  <meta name="copyright" content="$this->copyright">
  <meta name="description" content="$arg[desc]">
  <meta name="Author" content="Barton L. Phillips, mailto:barton@bartonphillips.com">
  <meta name="keywords" content="poker" />
{$arg['link']}
  <style>
body {
        background-color: lightblue;
}
header {
  text-align: center;
}
.extra {
        color: white;
        background-color: blue;
}
#memberstbl {
        border: 1px solid black;
        background-color: white;
}
#memberstbl th, #memberstbl td {
        padding: 4px;
        border: 1px solid black;
}
.map {
        color: white;
        background-color: blue;
}
#popup td, #popup th {
        border: 1px solid black;
}
#todayGuests, #todayGuests * {
        background-color: white;
        border: 1px solid black;
}
#todayGuests * {
        padding: 5px;
}
#wrapper {
}
#left {
        float: left;        
}
#right {
        float: right; margin-right: 50px;
}
  </style>
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script>
// Do anamation of header  
jQuery(document).ready(function($) {
  $("#pokerflush").animate({ 
                             opacity: 1.0,
                             left: 0
                           }, {duration: 5000 });
});
  </script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;


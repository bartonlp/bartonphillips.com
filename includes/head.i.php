<?php
return <<<EOF
<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf-8'>
  <meta name="copyright" content="$this->copyright">
  <meta name="Author"
    content="$this->author">
  <meta name="description"
    content="{$arg['desc']}">
  <meta name="keywords"
    content="Barton Phillips, Granby, Applitec Inc., Rotary, Programming, Tips and tricks, blog">
  <link rel="canonical" href="http://www.bartonphillips.com">
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="favicon.ico">
  <!-- Custom CSS -->
  <!-- Use CSS from bartonlp.com -->
  <link rel="stylesheet" href="http://bartonphillips.net/css/blp.css" title="blp default">
{$arg['link']}
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
  <script>
var lastId = "$this->LAST_ID";
  </script>
  <script src="http://bartonphillips.net/js/tracker.js"></script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

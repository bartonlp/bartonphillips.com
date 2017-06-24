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
  <!-- Custom CSS -->
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css" title="blp default">
{$arg['link']}
  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<!--  <script>
var lastId = "$this->LAST_ID";
  </script>
  <script src="https://bartonphillips.net/js/tracker.js"></script>
-->
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

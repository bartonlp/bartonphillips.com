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
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="https://bartonphillips.net/images/favicon.ico">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css" title="blp default">
{$arg['link']}
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.0.1.js"
  integrity="sha256-VvnF+Zgpd00LL73P2XULYXEn6ROvoFaa/vbfoiFlZZ4="
  crossorigin="anonymous"></script>
  <script>
jQuery.migrateMute = false;
jQuery.migrateTrace = false;
  </script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

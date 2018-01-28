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
  <link rel="canonical" href="https://www.bartonphillips.com">
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="https://bartonphillips.net/images/favicon.ico">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css" title="blp default">
  <!-- css is not css but a link to tracker via .htaccess RewriteRule. -->
  <link rel="stylesheet" type="text/css" href="/csstest-{$this->LAST_ID}.css" title="blp test">
{$arg['link']}
  <!-- jQuery -->
  <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>-->
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.0.1.js"
  integrity="sha256-VvnF+Zgpd00LL73P2XULYXEn6ROvoFaa/vbfoiFlZZ4="
  crossorigin="anonymous"></script>
  <script>
jQuery.migrateMute = false;
jQuery.migrateTrace = false;
var lastId = "$this->LAST_ID";
  </script>
  <script src="https://bartonphillips.net/js/tracker.js"></script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

<?php
// BLP 2018-05-11 -- ADD 'NO GOOD MSIE' message
  
if(!$this->noTrack) {
  $trackerStr = '<script src="https://bartonphillips.net/js/tracker.js"></script>';
} else {
  $trackerStr = '';
}

if(!$arg['keywords']) {
  $arg['keywords'] = "Barton Phillips, Programming, Weather Station, Tips and Tutorials, BLOG, About the Internet";
}

return <<<EOF
<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf-8'>
  <meta name="copyright" content="$this->copyright">
  <meta name="Author" content="$this->author">
  <meta name="description" content="{$arg['desc']}">
  <meta name="keywords" content="{$arg['keywords']}">
  <!-- More meta data -->
{$arg['meta']}
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="https://bartonphillips.net/images/favicon.ico">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css" title="blp default">
  <!-- css is not css but a link to tracker via .htaccess RewriteRule. -->
  <link rel="stylesheet" href="/csstest-{$this->LAST_ID}.css" title="blp test">
{$arg['link']}
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.3.2.min.js"></script>
  <script>
jQuery.migrateMute = false;
jQuery.migrateTrace = false;
  </script>
  <script>var lastId = "$this->LAST_ID";</script>
  $trackerStr
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

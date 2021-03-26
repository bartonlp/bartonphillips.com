<?php
// BLP 2021-03-26 -- Added logic to not do tracker stuff if nodb or noTrack set

if($this->noTrack === true || $this->nodb === true) {
  $trackerStr = '';
} else {
  $trackerStr =<<<EOF
<script data-lastid="$this->LAST_ID" src="https://bartonphillips.net/js/tracker.js"></script>
EOF;
} 

// BLP 2021-03-26 -- some sites may have a different defaultCss. If not set use our default

if(!$this->defaultCss) {
  $this->defaultCss = 'https://bartonphillips.net/css/blp.css';
}

// BLP 2021-03-26 -- default favicon

if(!$this->favicon) {
  $this->favicon = "https://bartonphillips.net/images/favicon.ico";
}

// BLP 2021-03-26 -- $arg takes president over $this from mysitemap.json

if(empty($arg['keywords'])) {
  $arg['keywords'] = $this->keywords;
}
if(empty($arg['title'])) {
  $arg['title'] = $this->title;
}
if(empty($arg['desc'])) {
  $arg['desc'] = $this->desc;
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
  <link rel="shortcut icon" href="$this->favicon">
  <!-- default CSS -->
  <link rel="stylesheet" href="$this->defaultCss" title="default">
{$arg['link']}
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.3.2.min.js"></script>
  <script>jQuery.migrateMute = false; jQuery.migrateTrace = false;</script>
$trackerStr
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

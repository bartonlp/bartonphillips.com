<?php

$pageHeadText = <<<EOF
<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf-8'/>
  <meta name="copyright" content="$this->copyright">
  <meta name="Author"
    content="Barton L. Phillips, mailto:bartonphillips@gmail.com"/>
  <meta name="description"
    content="{$arg['desc']}"/>
  <meta name="keywords"
    content="Barton Phillips, Granby, Applitec Inc., Rotary, Programming, Tips and tricks, blog"/>
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="http://www.bartonphillips.org/favicon.ico" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="/rssfeed.xml" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/blp.css" title="blp default" />
{$arg['link']}
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script async src="js/tracker.js"></script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;


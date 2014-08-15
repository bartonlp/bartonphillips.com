<?php
// Include GoogleAnalytics
//include_once(DOC_ROOT .'/analyticstracking.php'); // sets $GoogleAnalytics

$pageHeadText = <<<EOF
<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf-8'/>
  <meta name="Author"
     content="Barton L. Phillips, mailto:barton@bartonphillips.org"/>
  <meta name="description"
     content="{$arg['desc']}"/>
  <meta name="keywords"
     content="Barton Phillips, Granby, Applitec Inc., Rotary, Programming,
        Poker, Tips and tricks, blog"/>
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="http://www.bartonphillips.org/favicon.ico" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="/rssfeed.xml" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/css/blp.css" title="blp default" />
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script async src="/js/tracker.js"></script>
  <!-- extra script/style -->
{$arg['extra']}
$GoogleAnalytics
</head>
EOF;
?>

<?php
$pageHeadText = <<<EOF
{$arg['preheadcomment']}<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta charset='utf-8'/>
  <meta name="Author"
     content="Barton L. Phillips, mailto:barton@bartonphillips.org"/>
  <meta name="description"
     content="{$arg['desc']}"/>
  <meta name="keywords"
     content="Barton Phillips, Granby, Applitec Inc., Rotary, Programming,
        RSS Generator, Poker, Tips and tricks, blog"/>
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="/rssfeed.xml" />
  <!-- CSS -->
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/css/blp.css" type="text/css" title="blp default" />
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="/js/tracker.js"></script>
  <!-- Custom Scripts -->
  <!-- extra script/style -->
{$arg['extra']}
</head>
EOF;
?>

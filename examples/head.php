<?php
// This is used by weewx-test.php
// Just makes things a little easier.

return <<<EOF
<head>
  <base href="https://www.bartonphillips.com/weewx/">
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta name=viewport content="width=device-width, initial-scale=1">
  <meta charset='utf-8'/>
  <meta name="copyright" content="$this->copyright">
  <meta name="Author"
    content="$this->author"/>
  <meta name="description"
    content="{$arg['desc']}"/>
  <meta name="keywords"
    content="Barton Phillips"/>
  <!-- ICONS, RSS -->
  <link rel="shortcut icon" href="https://bartonphillips.net/images/favicon.ico" />
  <!-- Custom CSS -->
  <!-- Use CSS from bartonlp.com -->
  <link rel="stylesheet" href="https://bartonphillips.net/css/blp.css" title="blp default" />
  <link rel="stylesheet" href="https://www.bartonphillips.com/weewx/weewx.css" />
{$arg['link']}
  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
{$arg['extra']}
{$arg['script']}
{$arg['css']}
</head>
EOF;

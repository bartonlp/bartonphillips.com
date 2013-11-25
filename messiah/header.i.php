<?php
$pageHeadText = <<<EOF
<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="Author"
     content="Barton L. Phillips, mailto:barton@bartonphillips.com"/>
  <meta name="description"
     content="{$arg['desc']}"/>

  <link rel="stylesheet" href="messiah.css" type="text/css" />

  <!-- jQuery from Google site -->
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <!-- script -->
  {$arg['extra']}
</head>
EOF;

?>
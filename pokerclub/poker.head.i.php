<?php
$pageHeadText = <<<EOF
<head>
  <title>{$arg['title']}</title>
  <meta name="description"
     content="{$arg['desc']}" />
  <meta name="keywords" content="poker" />

  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

  {$arg['extra']}
</head>
EOF;
?>

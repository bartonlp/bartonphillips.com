<?php
define('HEAD_I_VERSION', "head.i.php-1.0.0");

/* BLP 2025-02-21 -  These are all set via $h or $this ($this is either from the constructor or
   mysitemap.json. 
  $h->title
  $h->base
  $h->viewport
  $h->charset
  $h->copyright
  $h->author
  $h->desc
  $h->keywords
  $h->meta
  $h->canonical
  $h->favicon
  $h->defaultCss
  $h->link
  $h->$jQuery // BLP 2025-02-21 - new
  $h->$trackerStr // BLP 2025-02-21 - new
  $h->extra
  $h->script
  $h->inlineScript
  $h->css
*/

// BLP 2023-09-07 - added to let me know if someone calls this directly.
if(!class_exists('dbPdo')) header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=bartonphillips.com&page=/head.i.php");

return <<<EOF
<head>
  <!-- bartonphillips.com/includes/head.i.php -->
  $h->title
  $h->base
  $h->viewport
  $h->charset
  $h->copyright
  $h->author
  $h->desc
  $h->keywords
  $h->meta
  $h->canonical
  $h->favicon
  $h->defaultCss
  $h->link
  $h->jQuery
  $h->trackerStr
  $h->extra
  $h->script
  $h->inlineScript
  $h->css
</head>
EOF;

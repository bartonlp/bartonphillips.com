<?php
/* BLP 2022-04-09 - These are all set via $h or $this ($this is either from the constructor or
   mysitemap.json. These are all made into full tags ie <title>$h->title</title>
   $h->base
   $h->title
   $h->desc
   $h->keywords
   $h->favicon
   $h->defaultCss
   $h->copyright (date added in constructor)
   $h->author
   $h->charset
   $h->viewport
   $h->defaultCss // if this is true then NO CSS otherwise if null then default blp.css, else vaue of defaultCss.
   $h->css // the <style>...</style> are added if it does not start with <style>
   $h->inlineScript // the <script>...</script> is added and not test is done as above.
   
   Currently $h->meta, $h->link, $h->extra, $h->script $h->inlineScript and $h->css have no $this value.
*/

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
  $h->canonical
  $h->meta
  $h->favicon
  $h->defaultCss
  $h->link
  $jQuery
  $trackerStr
  $h->extra
  $h->script
  $h->inlineScript
  $h->css
</head>
EOF;

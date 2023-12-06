<?php
// Add the page to tracker entry
// This is included after $S is instantiated

// I could put the filename into the $__info array and not do anything here. I would do it all in
// SiteClass and Database. Maybe later.

if(($__name = array_intersect([pathinfo($S->self)['filename']], ["altorouter", "loader"])[0]) !== null) {
  echo "name=$__name<br>";
  if(preg_match("~^.*(/.*?/.*?/.*)$~", $__FILENAME, $m)) {
    vardump("m", $m);
    $__file = "($__name)>$m[1]";
    echo "$__file<br>";
    $S->query("update $S->masterdb.tracker set page='$__file' where id=$S->LAST_ID");
  }
}

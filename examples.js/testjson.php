<?php
$_site = require_once(getenv("SITELOADNAME"));
$_site->nodb = true;
//vardump($_site);
$S = new $_site->className($_site);

$h->script =<<<EOF
<script type="application/ld+json">{"url":"https://www.tobinjames.com","name":"Tobin James Cellars","description":"<p>Built from the ground up on the site of an old stagecoach stop, it might seem more natural to stroll into the old-fashioned western-style saloon, place your booted foot on the brass rail of the bar and order a shot of whiskey from the bartender. However, in the Tobin James tasting room, award winning wines are poured from behind the grand, antique 1860's Brunswick mahogany bar from Blue Eye, Missouri.</p><p>Tobin James Cellars, located 8 miles east of Paso Robles on Highway 46, sits among 71 acres of vineyard's and oak dotted hills. A magnificent setting for magnificent wines! As soon as you approach, you see something unique. The wooden structures, heralded by the rotating blades of a towering water pump, have a character all their own, There is even a restored stage coach stop that is now being used as a guest house that will take your breath away. It seems everything structural is relevant of age and history.</p><p>Come visit Tobin James Cellars for a truly unique wine tasting experience, or call us for a delivery and find out for yourself why our wines are \"Paso Robles in a glass\"! You will see why this Paso Robles Winery is a favorite stop for locals and visitors alike.</p><p>\u00A0</p>","image":"//static1.squarespace.com/static/5a74c8082aeba5f9c4e3592b/t/5f29d2176f6a562830e78c58/1616696814346/","@context":"http://schema.org","@type":"WebSite"}</script>
EOF;
$b->script =<<<EOF
<script>
'use strict';

jQuery(document).ready(function($) {
  let x = $("script[type*='json']").text();
  console.log("x: " + x);
  let y = JSON.parse(x);
  console.log("y: ", y);
});
</script>
EOF;

[$top, $footer] = $S->getPageTopBottom($h, $b);

echo <<<EOF
$top
<h1>Test</h1>
$footer
EOF;

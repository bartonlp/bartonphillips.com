<?php
// BLP 2023-02-25 - use new approach
// Show Versions

$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$tbl = (require(SITECLASS_DIR . "/whatisloaded.class.php"))[0];

$S->title = "Display SiteClass Versions";
$S->banner = "<h1>$S->title</h1>";
$S->css = <<<EOF
td { padding: 5px }
pre { display: none; background: lightgray; padding: 5px 10px; overflow-x: scroll }
button { border-radius: 6px; margin-bottom: 20px; background: green; color: white; font-size: var(--blpFoneSize); }
EOF;

$S->b_inlineScript = <<<EOF
$("button").on("click", function() {
  if(this.flag) {
    $("pre").hide();
    $("button").html("Show \$_site object");
  } else {
    $("pre").show();
    $("button").html("Hide \$_site object");
  }
  this.flag = !this.flag;;
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

$site = "<pre>The <b>\$_site</b> object:\n" . escapeltgt(print_r($_site, true)) . "</pre>";

// Normally if we are using Database we would not be printing anything or at leas not much.
// \$top and \$footer are null if we are using Database.

echo <<<EOF
$top
<hr>
<p>This program displays all of the version numbers for the various classes used by the SiteClass framework.</p>
<button>Show \$_site object</button></p>
$site
$tbl
<hr>
$footer
EOF;
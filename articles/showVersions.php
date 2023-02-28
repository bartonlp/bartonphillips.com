<?php
// BLP 2023-02-25 - use new approach
// Show Versions

$_site = require_once(getenv("SITELOADNAME"));

if(strpos($_SERVER['QUERY_STRING'], "Database") === 0) {
  $S = new Database($_site);
} else {
  $S = new SiteClass($_site);
}

$T = new dbTables($S);

if($T) {
  $dbTables = $T->getVersion();
  $dbTablesMsg = "Instantiate \$T = new dbTables(\$S)";
}

// By requiring tracker.php and beacon.php I get access to the version numbers because if $_site
// has already been loaded the programs just return the version numbers. I really do not need to
// have the programs return the version as the defines are in effect just by requiring the programs.

$trackerVersion = require(SITECLASS_DIR . "/tracker.php"); // Get tracker version
$beaconVersion = require(SITECLASS_DIR . "/beacon.php"); // Get beacon
$helperVersion = HELPER_FUNCTION_VERSION;

$S->title = "Display SiteClass Versions";
$S->banner = "<h1>$S->title</h1>";
$S->css = <<<EOF
td { padding: 5px }
pre { display: none; background: lightgray; padding: 5px 10px; overflow-x: scroll }
button { border-radius: 6px; margin-bottom: 20px; background: green; color: white; font-size: var(--blpFoneSize); }
EOF;

$S->b_inlineScript = <<<EOF
  $("#siteclass tbody").append("<tr><td>Trackerjs version</td><td>" + TRACKERJS_VERSION + "</td></tr>");
  $("button").on("click", function() {
    if(this.flag) {
      $("pre").hide();
      $(this).html("Show \$_site object");
    } else {
      $("pre").show();
      $(this).html("Hide \$_site object");
    }
    this.flag = !this.flag;
  });
EOF;

if(method_exists($S, "getPageTopBottom")) { // If we instantiated Database instead this will not exist
  [$top, $footer] = $S->getPageTopBottom();
  $siteClassMsg = "\$S = new SiteClass(\$_site)";
} else {
  $siteClassMsg = "\$S = new Database(\S_site)";
}

// This is a little trick. We get the static getVersion for the $class.

$getVersion = function($class) {
  return $class::getVersion();
};

$site = "<pre>The <b>\$_site</b> object:\n" . escapeltgt(print_r($_site, true)) . "</pre>";

// Normally if we are using Database we would not be printing anything or at leas not much.
// $top and $footer are null if we are using Database.

echo <<<EOF
$top
<hr>
<p>This program displays all of the version numbers for the various classes used by the SiteClass framework.</p>
<p>Instantiate $siteClassMsg<br>
$dbTablesMsg</p>
<button>Show \$_site object</button>
$site<br>
<table id="siteclass" border="1">
<tbody>
<tr><td>\$S version</td><td>{$S->getVersion()}</td></tr>
<tr><td>SiteClass version</td><td> {$getVersion("SiteClass")}</td></tr>
<tr><td>\$S->db version</td><td> {$S->db->getVersion()}</td></tr>
<tr><td>MySqli version</td><td> {$getVersion("dbMysqli")}</td></tr>
<tr><td>Abstract version</td><td> {$getVersion("dbAbstract")}</td></tr>
<tr><td>Database version</td><td> {$getVersion("Database")}</td></tr>
<tr><td>ErrorClass version</td><td> {$getVersion("ErrorClass")}</td></tr>
<tr><td>SqlException version</td><td> {$getVersion("SqlException")}</td></tr>
<tr><td>\$S->dbTables</td><td> $dbTables</td></tr>
<tr><td>\$T version</td><td> $dbTables</td></tr>
<tr><td>dbTables version</td><td> {$getVersion("dbTables")}</td></tr>
<tr><td>Helper functions version</td><td> $helperVersion</td></tr>
<tr><td>SiteLoad version</td><td> $_site->siteloadVersion</td></tr>
<tr><td>SiteLoad version static</td><td> {$getVersion("siteload\getinfo")}</td></tr>
<tr><td>Beacon version</td><td> $beaconVersion</td></tr>
<tr><td>Tracker version</td><td> $trackerVersion</td></tr>
<tr><td>WhatIsLoaded version</td><td> {$getVersion("whatis\WhatIsLoaded")}</td></tr>
</tbody>
</table>
</p>
<hr>
$footer
EOF;
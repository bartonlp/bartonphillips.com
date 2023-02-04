<?php
// Show Versions

$_site = require_once(getenv("SITELOADNAME"));
//vardump("site", $_site);
$S = new SiteClass($_site);
//$S = new Database($_site);
$T = new dbTables($S);

//vardump("\$S", $S);

if($T) {
  $dbTables = $T->getVersion();
  $dbTablesMsg = "Instantiate \$T = new dbTables(\$S)";
}

// By requiring tracker.php and beacon.php I get access to the version numbers because if $_site
// has already been loaded the programs just return the version numbers. I really do not need to
// have the programs return the version as the defines are in effect just by requiring the programs.

/*$trackerVersion = */ require(SITECLASS_DIR . "/tracker.php");
$trackerVersion = TRACKER_VERSION;
$beaconVersion = require(SITECLASS_DIR . "/beacon.php");
$helperVersion = HELPER_FUNCTION_VERSION;

$h->title = "Display SiteClass Versions";
$h->banner = "<h1>$h->title</h1>";
$h->css = <<<EOF
td { padding: 5px }
EOF;

$b->inlineScript = <<<EOF
  $("#siteclass tbody").append("<tr><td>Trackerjs version</td><td>" + TRACKERJS_VERSION + "</td></tr>");
EOF;

if(method_exists($S, "getPageTopBottom")) {
  [$top, $footer] = $S->getPageTopBottom($h, $b);
}

// This is a little trick. We get the static getVersion for the $class.

$getVersion = function($class) {
  return $class::getVersion();
};

//$site = "<pre>" . print_r($_site, true) . "</pre>";

echo <<<EOF
$top
<hr>
$site
<p>This program displays all of the version numbers for the various classes used by the SiteClass framework.</p>
<p>Instantiate \$S = new SiteClass(\$_site)<br>
$dbTablesMsg</p>

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
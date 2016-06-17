<?php
// This is CLI and run as a cron job to aggregate the web statistics for a day.
// The results are used by the webstats.php program in our document root directory.

$_site = require_once(getenv("HOME")."/includes/siteautoload.class.php");

$S = new SiteClass($_site);

$S->setSiteCookie("NEW", date("Y-m-d H:i:s"), time() + 10*60, "/scripts/");
vardump($_COOKIE);

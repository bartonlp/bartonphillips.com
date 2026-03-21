<?php
/* Items used by SiteClass. These can be set with the SiteClass variable (usually $S)
   or with the value returned from 'require_once' for 'siteload.php' (usually $_site).
   These are both objects so $S->title = 'new title' or $_site->title = 'new title' will set the '$h->title'.
   The following can be set with either $S or $_site:
      title
      desc
      keywords
      copyright
      author
      charset
      viewport
      canonical
      meta
      favicon
      defaultCss
      css
      cssLink
      inlineScript
      script
      link
      extra
      preheadcomment
      lang
      htmlextra
      trackerLocationJs
      interactionLocationJs
      interactionLocationPhp
      trackerLocation
      beaconLocation
      logoImgLocation
      headerImg2Location
      trackerImg1
      trackerImgPhone
      trackerImg2
      trackerImgPhone2
      mysitemap

   These disable certain functions.
   For example, if you set $S->nojquery the 'jQuery' JavaScript file and the 'tracker.js' file will not be included.
      nojquery, Don't load jQuery and tracker.js.
      noGeo, Don't load the Google geolocation JavaScript logic.
      nointeraction, Don't do interaction logging via logging.php and logging.js
      noCssLastId, Don't 
      nofooter,
      noAddress,
      noCopyright,
      noEmailAddress,
      noCounter,
      noLastmod,
      nonce, this is the nonce for Content-Security-Policy. Currently only bartonphillips.com/index.php uses it.

   These must be done via $_site or they wont work as expected:
      noTrack,
      nodb,
   
   For example, $S->noTrack will not be available for the Class constructor
   so you must do $_site->noTrack before instantiating the Class.
*/

define('HEAD_I_VERSION', "head.i.php-1.0.1");

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
  $h->cssLink
  $h->link
  $h->jQuery
  $h->trackerStr
  $h->extra
  $h->script
  $h->inlineScript
  $h->css
</head>
EOF;

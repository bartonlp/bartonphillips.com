<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new SiteClass($_site);

$S->h_inlineScript =<<<EOF
const mylastId = $("script[data-lastid]").attr("data-lastid");                    

jQuery(document).ready(function($) {
  $("#logo").remove();
  let newimage = "https://bartonphillips.net" + image;
  $("header a:first-of-type").html("<img id='logo' src=" + newimage + ">");

//  let image = "https://bartonphillips.net/images/" + $("#logo").attr("data-image");
//  $("#logo").attr("src",image);
  $.ajax({
    url: trackerUrl,
    data: {page: 'test', 'id': lastId, site: thesite, ip: theip, thepage: thepage, isMeFalse: isMeFalse},
    type: 'post',
    success: function(data) {
       console.log(data);
    },
    error: function(err) {
      console.log(err);
    }
  });
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
$footer
EOF;

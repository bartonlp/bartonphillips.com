<?php
$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->h_inlineScript =<<<EOF
jQuery(document).ready(function($) {
  var image = new Image;
  image.crossOrigin = "Anonymous";
  image.src = "https://bartonphillips.net/images/CIMG0020.JPG";
  
  $(image).load(function() {
    const canvas = document.createElement("canvas");

    canvas.width = 300;
    canvas.height = 200;
    canvas.getContext("2d").drawImage(this, 0, 0, 300, 200);

    const dataUri = canvas.toDataURL();
    console.log("URL:", dataUri);
    const myimg = new Image;
    myimg.src = dataUri;
    $("#top").append(myimg);
  });
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<div id="top">
<h1>Canvas Test</h1>
<img id="image" src="https://bartonphillips.net/images/CIMG0020.JPG">
</div>
$footer
EOF;

<?php
// BLP 2021-10-31 -- goto: https://leafletjs.com/examples/quick-start/
// You will need an up to date accessToken from https://account.mapbox.com/access-tokens/
// You may need to login to mapbox.com: Account: bartonphillips@gmail.com, Password: 709Blp8653

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$S->link =<<<EOF
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
   integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
   crossorigin=""/>
EOF;

$S->css =<<<EOF
#mapid {
  height: 700px;
}
EOF;

$S->b_script =<<<EOF
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
  integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
  crossorigin="">
</script>
<script>  
jQuery(document).ready(function($) {
  var mymap = L.map('mapid').setView([35.110852, -77.117462], 13);
  var marker = L.marker([35.110852, -77.117462]).addTo(mymap);
  marker.bindPopup("<b>Barton and Bonnie's Home</b>").openPopup();

  var popup = L.popup();

  function onMapClick(e) {
    popup
    .setLatLng(e.latlng)
    .setContent("You clicked the map at " + e.latlng.toString())
    .openOn(mymap);
  }

  mymap.on('click', onMapClick);

  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoiYmFydG9ubHAiLCJhIjoiY2t2ZjBtNzl5Ym5xczJ2bnpnaWlidjVvaCJ9.Z0Odk3_BG1T0_iP9LT66HQ'
}).addTo(mymap);
});
</script>
EOF;

$S->title = "Barton&Bonnie's Home";
$S->banner = "<h1>$S->title</h1>";

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<div id="mapid"></div>
$footer
EOF;

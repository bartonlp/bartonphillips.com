<?php
// https://leafletjs.com/reference-1.3.0.html#url-template

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$h->link =<<<EOF
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
    integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
    crossorigin=""/>
EOF;

$h->css =<<<EOF
  <style>
#mapid {
  height: 700px;
}
  </style>
EOF;

$h->script =<<<EOF
  <!-- Make sure you put this AFTER Leaflet's CSS -->
  <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
    integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
    crossorigin=""></script>

  <script>
jQuery(document).ready(function($) {
  var mymap = L.map('mapid').setView([35.110966, -77.092889], 13);
  var marker = L.marker([35.110966, -77.092889]).addTo(mymap);
  marker.bindPopup("<b>Barton's Villa</b><br>Or is it a prison?").openPopup();

  var popup = L.popup();

  function onMapClick(e) {
    popup
    .setLatLng(e.latlng)
    .setContent("You clicked the map at " + e.latlng.toString())
    .openOn(mymap);
  }

  mymap.on('click', onMapClick);

  L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, '+
     '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; ' +
     '<a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox.streets',
    accessToken: 'pk.eyJ1IjoiYmFydG9ubHAiLCJhIjoiY2poZGp5NXN4MGMxdTNkbWxrYWR0em1mMiJ9.Ilrp-SUZvMW8aHqW_A5nAw'
  }).addTo(mymap);
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<div id="mapid"></div>
$footer
EOF;

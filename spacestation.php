<?php
require_once("/var/www/includes/siteautoload.class.php");

$S = new Blp;

$h->title = "ISS Info";
$h->banner = "<h1>International Space Station Information</h1>";
$h->extra =<<<EOF
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
  <script src='http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js'></script>
EOF;

$h->css =<<<EOF
  <style>
#map {
  height: 600px;
}
  </style>
EOF;

// head.i.php loads jQuery

$h->script =<<<EOF
  <script>
jQuery(document).ready(function($) {
  var map = L.map('map').setView([0,0], 2);

  function moveISS () {
    $.getJSON('http://api.open-notify.org/iss-now.json?callback=?', function(data) {
      var lat = data['iss_position']['latitude'];
      var lon = data['iss_position']['longitude'];
      $("#latlon").html("Lat:&nbsp;&nbsp;"+lat+"<br>Lon: "+lon);

      iss.setLatLng([lat, lon]);
      isscirc.setLatLng([lat, lon]);
      map.panTo([lat, lon], animate=true);
    });
    setTimeout(moveISS, 10000); 
  }

  L.tileLayer('http://open-notify.org/Open-Notify-API/map/tiles/{z}/{x}/{y}.png', {
    maxZoom: 4,
  }).addTo(map);

  var ISSIcon = L.icon({
    iconUrl: 'http://open-notify.org/Open-Notify-API/map/ISSIcon.png',
    iconSize: [50, 30],
    iconAnchor: [25, 15],
    popupAnchor: [50, 25],
    shadowUrl: 'http://open-notify.org/Open-Notify-API/map/ISSIcon_shadow.png',
    shadowSize: [60, 40],
    shadowAnchor: [30, 15]
  });


  var iss = L.marker([0, 0], {icon: ISSIcon}).addTo(map);
  var isscirc = L.circle([0,0], 2200e3, {color: "#c22", opacity: 0.3, weight:1, fillColor: "#c22", fillOpacity: 0.1}).addTo(map); 

  moveISS();

  $.getJSON('http://api.open-notify.org/iss-pass.json?lat=34.190962&lon=-118.956400&alt=219.151&n=5&callback=?', 
    function(data) {
      //console.log(data);
      data['response'].forEach(function (d) {
        var date = new Date(d['risetime']*1000);
        $('#isspass').append('<li>' + date.toDateString() + ' ' +
          date.toLocaleTimeString('en-US', { hour12: true }) + '</li>');
      });
  });
});
  </script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p><a href="http://www.bartonphillips.com/weewx">My Home Weather Station</a></p>
<div>
<table>
<tr><td>Latitude:</td><td>34.190962</td></tr>
<tr><td>Longitude:</td><td>-118.9564</td></tr>
<tr><td>Altitude:</td><td>719 ft (219 m)</td></tr>
</table>
</p>
<h3>Internatinal Space Station will be overhead at these times:</h3>
<ul id='isspass'></ul>

<div>
<h3>Where is the space station now?</h3>
<p id="latlon"></p>
<div id='map'></div>
</div>
<a href="http://www.isstracker.com/">Another ISS Tracker Map</a>
<hr>
$footer
EOF;


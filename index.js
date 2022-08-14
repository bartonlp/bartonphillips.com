// index.js
// BLP 2022-04-18 - Remove git stuff
// This is the JavaScript for index.php
// BLP 2021-03-26 --
// This is installed after the footer because we set $b->script not $h.
// As a result we do not need to do jQuery(document).ready(function($) {

'use strict';

// BLP 2022-07-19 - NOTE fingers is not used here it is used in geo.js
// which is loaded by SiteClass::getPageHead();

var fingers; // Set in index.php via index.i.php so it can be used in geo.js

// If we have adminstuff we need another column.

if(window.CSS) {
  if(CSS.supports('display', 'grid') && $("#adminstuff").length != 0) {
    $("#grid-section").css("grid-template-columns", "repeat(4, 1fr)");
  }
}

// Local date/time for 'Today is' & 'Your Time is'. Uses phpdate.js
// loaded in index.php

setInterval(function() {
  var d = date("l F j, Y");
  var t = date("H:i:s T"); // from phpdate.js
  $("#datetoday").html("<span class='green'>"+
                       d+"</span><br>Your Time is: <span class='green'>"+
                       t+"</span>");
}, 1000);

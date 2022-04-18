// index.js
// BLP 2022-04-18 - Remove git stuff
// This is the JavaScript for index.php
// BLP 2021-03-26 --
// This is installed after the footer because we set $b->script not $h.
// As a result we do not need to do jQuery(document).ready(function($) {

'use strict';

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
